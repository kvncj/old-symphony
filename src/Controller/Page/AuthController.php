<?php

namespace App\Controller\Page;

use App\Controller\ExtendedController;
use App\Entity\Auth\ResetPasswordRequest;
use App\Entity\Team;
use App\Entity\TeamRole;
use App\Entity\User;
use App\Model\Email\EmailTemplate;
use App\Model\Email\RecipientList;
use App\Model\Exception\Enum\FlashLevel;
use App\Model\Exception\FlashException;
use App\Model\User\Enum\TeamPosition;
use App\Model\User\Enum\UserStatus;
use App\Model\User\Enum\UserType;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

#[Route(name: 'auth')]
class AuthController extends ExtendedController
{
    use ResetPasswordControllerTrait;

    public function index(Request $request): Response
    {
        return $this->render('@Page/auth/index.html.twig', $request->attributes->all());
    }

    #[Route('/login', name: '_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error != null)
            $this->addFlash(FlashLevel::DANGER->value, $error->getMessage());

        return $this->forward(AuthController::class . "::index", [
            'path' => 'login',
            'last_username' => $authenticationUtils->getLastUsername()
        ]);
    }

    #[Route('/logout', name: '_logout')]
    public function logout()
    {
        return $this->redirectToRoute('login');
    }

    #[Route('/register', name: '_register')]
    public function register(Request $request, EmailService $emailService, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, VerifyEmailHelperInterface $verifyHelper): Response
    {
        $requestData = $request->request->all();
        $formData = $requestData['register'] ?? null;
        if ($formData) {
            try {
                $token = $formData['_csrf_token'];
                if (!$this->isCsrfTokenValid('register', $token))
                    throw FlashException::danger('Invalid form token.');

                if (count(array_unique($formData['password'])) != 1)
                    throw FlashException::danger('Invalid form password.');
                $password = array_shift($formData['password']);

                $uppercase = preg_match('@[A-Z]@', $password);
                $lowercase = preg_match('@[a-z]@', $password);
                $number    = preg_match('@[0-9]@', $password);
                $specialChars = preg_match('@[^\w]@', $password);

                if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8)
                    throw FlashException::danger('Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.');

                $email = $formData['email'];
                $emailValid = (bool)(filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE));
                if (!$emailValid)
                    throw FlashException::danger('Invalid e-mail address.');

                $emailParts = explode('@', $email);
                $domain = end($emailParts);
                if (!in_array($domain, ['gmail.com', 'yahoo.com', 'hotmail.com', 'aol.com', 'msn.com'])) {
                    if (!checkdnsrr($domain))
                        throw FlashException::danger('Invalid e-mail address.');
                }

                /** @var UserRepository $userRepo */
                $userExists = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($userExists)
                    throw FlashException::danger('The email address has already been registered.');

                $user = new User();
                $user->setEmail($formData['email']);
                $user->setType(UserType::tryFrom($formData['type']) ?? UserType::INDIVIDUAL);
                $user->setPassword($passwordHasher->hashPassword($user, $password));

                $team = new Team();
                $team->setName($user->getEmail());

                $role = new TeamRole();
                $role->setMember($user);
                $role->setTeam($team);
                $role->setRole(TeamPosition::OWNER);

                $em->persist($user);
                $em->persist($team);
                $em->persist($role);

                $em->flush();

                $signatureComponents = $verifyHelper->generateSignature('auth_verify', $user->getId(), $user->getEmail(), ['id' => $user->getId()]);
                $verificationURL = $signatureComponents->getSignedUrl();

                /**
                 * Create and send verification e-mail
                 */
                $email = new EmailTemplate();
                $email->setSubject('Verify your account.');
                $email->setContent(strstr($this->render('email/verify-user.html.twig', [
                    'name' => 'Pandorabox Mail',
                    'email' => 'noreply@pandorabox.com.my',
                    'verification_url' => $verificationURL
                ]), '<head>'));

                $recipients = new RecipientList();
                $recipients->addRecipient($user->getUserIdentifier(), $user->getEmail());

                $emailService->sendEmail($email, $recipients);

                $this->addFlash('success', 'A verification link has been sent to your email address.');
            } catch (\Exception $e) {
                if (isset($user) && $user->getId() != null) {
                    $em->remove($user);
                    $em->remove($team);
                    $em->remove($role);
                    $em->flush();
                }
                $this->handleException($e, "Error processing user registration. Please try again later.");
            }
        }

        return $this->forward(AuthController::class . "::index", [
            'path' => 'register'
        ]);
    }

    #[Route(path: '/verify', name: '_verify')]
    public function verifyUser(Request $request, EntityManagerInterface $em, VerifyEmailHelperInterface $verifyHelper)
    {
        try {
            $id = $request->get('id');
            if (null === $id)
                throw new FlashException('Invalid verification link.');

            $user = $em->getRepository(User::class)->find($id);
            if (!$user instanceof User)
                throw new FlashException('User not found. Please try registering again.');

            $verifyHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
            $user->setStatus(UserStatus::COMPANY_UNSET);
            $user->setRoles(['ROLE_USER']);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Your account has been verified. You may now login.');
            return $this->redirectToRoute('auth_login');
        } catch (\Exception $e) {
            if (isset($user) && $em->contains($user)) {
                $em->remove($user);
                $em->flush();
            }
            $caseNo = $this->handleException($e);
            $this->addFlash('danger', "$caseNo: There has been an error in verifying your account. Please try again later.");
            return $this->redirectToRoute('auth_register');
        }
    }

    #[Route('/forgot', name: '_forgot')]
    public function forgotPassword(Request $request, EmailService $emailService, EntityManagerInterface $em, ResetPasswordHelperInterface $resetPasswordHelper): Response
    {
        $requestData = $request->request->all();
        $formData = $requestData['forgot'] ?? null;
        if ($formData) {
            try {
                $token = $formData['_csrf_token'];
                if (!$this->isCsrfTokenValid('forgot', $token))
                    throw FlashException::danger('Invalid form token.');

                $emailAddress = $formData['email'];
                $emailValid = filter_var($emailAddress, FILTER_VALIDATE_EMAIL) ? $emailAddress : filter_var($emailAddress, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
                if (!$emailValid)
                    throw FlashException::danger('Invalid e-mail address.');

                $user = $em->getRepository(User::class)->findOneBy(['email' => $emailAddress]);
                if (!$user instanceof User)
                    throw FlashException::danger('Provided e-mail address is not registered.');

                $resetToken = $resetPasswordHelper->generateResetToken($user);

                $email = new EmailTemplate();
                $email->setSubject('Your password reset request');
                $email->setContent(strstr($this->render('email/reset-password.html.twig', [
                    'name' => 'Pandorabox Mail',
                    'email' => 'noreply@pandorabox.com.my',
                    'resetToken' => $resetToken,
                ]), '<head>'));

                $recipients = new RecipientList();
                $recipients->addRecipient($user->getUserIdentifier(), $user->getEmail());

                $emailService->sendEmail($email, $recipients);

                $this->addFlash('success', "A reset link has been sent to the provided email address.");
            } catch (TooManyPasswordRequestsException $e) {
                $this->addFlash('warning', "A password reset request has already been created for this account.");
            } catch (\Exception $e) {
                throw $e;
                if (isset($user)) {
                    $resetRequests = $em->getRepository(ResetPasswordRequest::class)->findBy(['user' => $user->getId()]);
                    if (!empty($resetRequests)) {
                        foreach ($resetRequests as $resetRequest)
                            $em->remove($resetRequest);
                        $em->flush();
                    }
                }
                $caseNo = $this->handleException($e);
                $this->addFlash('danger', "$caseNo: There has been an error submitting your request. Please try again later.");
            }
        }

        return $this->forward(AuthController::class . "::index", [
            'path' => 'forgot-password'
        ]);
    }

    #[Route('/reset-password/{token}', name: '_reset', methods: ['GET', 'POST'])]
    public function resetPassword(Request $request, EntityManagerInterface $em, ResetPasswordHelperInterface $resetPasswordHelper, UserPasswordHasherInterface $passwordHasher, string $token = null): Response
    {
        if ($token) {
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('auth_reset');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            $this->addFlash('danger', '_csrf_token not found in request.');
            return $this->redirectToRoute('passwordForgot');
        }

        $user = $resetPasswordHelper->validateTokenAndFetchUser($token);
        $requestData = $request->request->all();
        $formData = $requestData['reset'] ?? null;
        if ($formData) {
            try {
                $csrfToken = $formData['_csrf_token'];
                if (!$this->isCsrfTokenValid('reset', $csrfToken))
                    throw FlashException::danger('Invalid form token.');

                if (count(array_unique($formData['password'])) != 1)
                    throw FlashException::danger('Invalid form password.');
                $password = array_shift($formData['password']);

                $uppercase = preg_match('@[A-Z]@', $password);
                $lowercase = preg_match('@[a-z]@', $password);
                $number    = preg_match('@[0-9]@', $password);
                $specialChars = preg_match('@[^\w]@', $password);

                if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8)
                    throw FlashException::danger('Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.');

                $encodedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($encodedPassword);
                $resetPasswordHelper->removeResetRequest($token);
                $em->flush();

                $this->cleanSessionAfterReset();
                $this->addFlash('success', 'Your password has been changed. You may now login.');
                return $this->redirectToRoute('auth_login');
            } catch (InvalidResetPasswordTokenException $e) {
                $this->addFlash('danger', 'Invalid token. Please attempt another reset password request.');
            } catch (\Exception $e) {
                $caseNo = $this->handleException($e);
                $this->addFlash('danger', "$caseNo: There has been an error in resetting your password. Please try again later.");
            }
        }
        return $this->forward(AuthController::class . "::index", [
            'path' => 'reset-password'
        ]);
    }
}

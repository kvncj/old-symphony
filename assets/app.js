/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import { startStimulusApp } from '@symfony/stimulus-bridge';
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));

import { registerVueControllerComponents } from '@symfony/ux-vue';
registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));
//registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/, 'lazy'));
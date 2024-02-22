/*let activeName = <string>(<HTMLInputElement>document.querySelector(`input#active`)).value

let forms = {
    login: <HTMLElement>document.querySelector(`form#login`),
    register: <HTMLElement>document.querySelector(`form#register`),
    forgot: <HTMLElement>document.querySelector(`form#forgot`)
}

async function loadForm(name: string) {
    let url = window.origin + `/${name}`
    fetch(url)
        .then(response => response.text())
        .then(body => {
            let parser = new DOMParser()
            let doc = <Document>parser.parseFromString(body, `text/html`)
            let targetForm = <HTMLElement>doc.querySelector(`form#${name}`)

            forms[name].parentNode.replaceChild(forms[name], targetForm)
            forms[name] = document.querySelector(`form#${name}`)
        })
        .catch(error => {
            console.log(error)
        })
}

function changeForm(name: string) {
    (<HTMLElement>forms[activeName]).classList.add(`display-transition-hidden`);
    (<HTMLElement>forms[name]).classList.remove(`display-transition-hidden`);
    activeName = name
}

function changeFormClick(e: Event) {
    changeForm((<HTMLElement>e.currentTarget).dataset.redirect ?? `login`)
}

// initialize
Object.entries(forms).forEach(
    ([name, form]) => {
        if (name != activeName) loadForm(name)
    }
);
changeForm(activeName)
document.querySelectorAll('button[data-redirect]').forEach((btn) => btn.addEventListener(`click`, changeFormClick))*/
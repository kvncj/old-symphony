const form = <HTMLFormElement>document.querySelector(`#product-edit-form`)

const currencyInputs: NodeListOf<HTMLSelectElement> = form.querySelectorAll(`#product-edit-form__price-currency`)
function currencyUpdated(ev: Event) {
    let selection = (<HTMLSelectElement>ev.currentTarget).value
    currencyInputs.forEach((element) => element.value = selection)
}
currencyInputs.forEach((element) => element.addEventListener(`change`, currencyUpdated))

const typeSelect = <HTMLSelectElement>form.querySelector(`#product-edit-form__type`)
const typeContainers = {
    simple: form.querySelector(`#product-edit-form__general--simple`),
    variable: form.querySelector(`#product-edit-form__general--variable`)
}
console.log(typeContainers)
function changeType(ev: Event) {
    console.log(typeSelect.value)
    for (const type in typeContainers) {
        let container = <HTMLElement>typeContainers[type]
        console.log(type == typeSelect.value)
        if (type != typeSelect.value) container.classList.add(`hidden`)
        else container.classList.remove(`hidden`)
    }
}
typeSelect.addEventListener(`change`, changeType)
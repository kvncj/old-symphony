<template>
    <div :id="form.id" class="flex">
        <div class="flex" ref="gallery" @dragover="dragOver">
            <template v-for="image, index in images" :key="image.key">
                <div class="gallery-image relative w-[100px] h-[100px]" draggable="true" :data-key="image.key"
                    @dragstart="dragStart" @dragend="dragEnd">
                    <input class="gallery-image__id" type="hidden" :name="`${form.name}[${index}][id]`"
                        :value="image.id" />
                    <input class="gallery-image__name" type="hidden" :name="`${form.name}[${index}][name]`"
                        :value="image.name" />
                    <input class="gallery-image__mime" type="hidden" :name="`${form.name}[${index}][mime]`"
                        :value="image.mime" />
                    <input class="gallery-image__url" type="hidden" :name="`${form.name}[${index}][url]`"
                        :value="image.url" />
                    <img :src="image.url">
                    <button type="button" class="absolute top-0 right-0" @click="remove">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
        <div ref="addBtn" class="w-[100px] cursor-pointer">
            <img @click="trigger"
                src="https://storage.googleapis.com/pandorabox_wpbuckets/woocommerce-placeholder.png">
        </div>
        <input type="file" ref="fileInput" @change="addImage" class="hidden" :multiple="form.multiple">
    </div>
</template>

<script lang="ts" setup>
import { onMounted, reactive, ref } from 'vue';

const props = defineProps<{
    form: {
        id?: string
        name: string
        multiple: boolean
    }
    images: Array<{
        key: Number;
        id: Number;
        name?: string;
        mime?: string;
        url: string;
    }>
}>()
const gallery = ref<InstanceType<typeof HTMLElement>>()
const addBtn = ref<InstanceType<typeof HTMLElement>>()
const fileInput = ref<InstanceType<typeof HTMLInputElement>>()

const multipleAllowed = props.form.multiple ?? false
console.log(multipleAllowed)

let dragging: HTMLElement | null = null

let images = reactive(props.images);

onMounted(() => {
    toggleAddBtn()
})

function addImage() {
    let fileInputInstance = <HTMLInputElement>fileInput.value
    let files = fileInputInstance.files
    if (null === files || files.length == 0) return

    for (let file of files) {
        let name = file.name
        if (file.size > 120000) {
            alert(name + " is over 1MB")
            continue
        }
        let reader = new FileReader()
        reader.readAsDataURL(file)
        reader.onload = function () {
            let image = new Image()
            image.src = <string>reader.result
            image.onload = function () {
                if (image.height > 1000 || image.width > 1000) {
                    alert(`Height or width of ${name} exceeds 1000px.`)
                    return false
                } else if (image.width / image.height != 1) {
                    alert(`Width and height of ${name} are not equal.`)
                    return false
                } else {
                    if (!multipleAllowed) {
                        images.splice(0)
                    }
                    images.push({ key: Date.now(), id: 0, name: name, mime: file.type, url: image.src })
                    toggleAddBtn()
                }
            }
        }
    }
}

function dragStart(ev: Event) {
    dragging = <HTMLElement>ev.currentTarget
}

function dragEnd(ev: Event) {
    dragging = null

    images.splice(0)
    let galleryInstance = <HTMLElement>gallery.value;
    (<Array<HTMLElement>>[...galleryInstance.children]).forEach((element) => {
        let key = parseInt(<string>element.dataset.key)
        images.push({
            key: key,
            id: parseInt((<HTMLInputElement>element.querySelector(`input.gallery-image__id`)).value),
            name: (<HTMLInputElement>element.querySelector(`input.gallery-image__name`)).value,
            mime: (<HTMLInputElement>element.querySelector(`input.gallery-image__mime`)).value,
            url: (<HTMLInputElement>element.querySelector(`input.gallery-image__url`)).value,
        })
    })
}

function dragOver(ev: DragEvent) {
    ev.preventDefault()
    if (dragging instanceof HTMLElement && dragging.classList.contains(`gallery-image`)) {
        let x = ev.clientX
        let y = ev.clientY

        let galleryInstance = <HTMLElement>gallery.value;
        let closest = <HTMLElement>[...galleryInstance.children].reduce((previous: Element, current: Element) => {
            const currentBox = current.getBoundingClientRect()
            const currentDiffX = x - currentBox.x
            const currentDiffY = y - currentBox.y
            const currentOffset = (currentDiffX * currentDiffX + currentDiffY * currentDiffY);

            const previousBox = previous.getBoundingClientRect()
            const previousDiffX = x - previousBox.x
            const previousDiffY = y - previousBox.y
            const previousOffset = (previousDiffX * previousDiffX + previousDiffY * previousDiffY);

            return currentOffset < previousOffset ? current : previous
        }, document.createElement(`div`))

        const closestBox = closest.getBoundingClientRect()
        const draggableBox = dragging.getBoundingClientRect()

        if (closestBox.x < draggableBox.x)
            closest.insertAdjacentElement(`beforebegin`, dragging)
        else
            closest.insertAdjacentElement(`afterend`, dragging)
    }
}

function toggleAddBtn(force: boolean = false) {
    console.log(`toggling`)
    let addBtnInstance = <HTMLElement>addBtn.value;
    if (force) addBtnInstance.style.display = `none`
    else addBtnInstance.style.display = (multipleAllowed || images.length == 0) ? `block` : `none`
}

function trigger() {
    fileInput.value?.click()
}

function remove(ev: Event) {
    let target = <HTMLElement>ev.currentTarget
    let image = <HTMLElement>target.parentElement

    let index = Number(image.dataset.index)
    images.splice(index, 1)

    toggleAddBtn()
}

</script>
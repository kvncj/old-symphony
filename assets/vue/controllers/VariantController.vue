

<template>
    <label class="block text-gray-700 text-sm font-bold mb-2">Options</label>
    <table ref="optionContainer" class="mb-4">
        <div v-if="options.length == 0">
            No options added.
        </div>
        <template v-for="option, index in options" :key="option.name">
            <tr>
                <td class="py-2">
                    <label
                        class="block text-gray-700 text-sm flex-initial font-bold my-auto mr-2">{{ option.name }}</label>
                    <input type="hidden" :name="`${props.form.name}[options][${index}][name]`" :value="option.name" />
                </td>
                <td class="py-2">
                    <TagInput 
                    :form="{ key2: option.name, name:`${props.form.name}[options][${index}][value]`, value:option.values.join(',') }"
                    :settings="{ options: computeOptionList(option.values) }" @change="calculateVariants" class="valueField" />
                </td>
                <td class="py-2">
                    <button type="button" @click="removeOption(index)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Remove
                        Option</button>
                </td>
            </tr>
        </template>
    </table>
    <button type="button" @click="addOption"
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold mb-4 py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add
        Option</button>

    <div ref="optionContainer" class="mb-4">
        <template v-for="variant, index in variants" :key="variant.name">
            <Accordion>
                <template #header>
                    <div>{{ variant.name }}
                        <input type="hidden" :name="`${props.form.name}[variants][${index}][name]`"
                            :value="variant.name" />
                    </div>
                </template>

                <template #default>
                    <div class="flex space-x-2 mb-4">
                        <div>
                            <ImageGallery :form="{ name: `${form.name}[variants][${index}][image]`, multiple: false }"
                                :images= "(variant.image == null ? [] : [variant.image])" />
                        </div>

                        <div>
                            <label :for="`${props.form.id}_variant_${index}_status`"
                                class="block text-gray-700 text-sm font-bold mb-2">Active</label>
                            <input :id="`${props.form.id}_variant_${index}_status`" type="checkbox" value="active"
                                :name="`${props.form.name}[variants][${index}][status]`" :checked="variant.status==`active`" />
                        </div>

                        <div>
                            <label :for="`${props.form.id}_variant_${index}_sku`"
                                class="block text-gray-700 text-sm font-bold mb-2">SKU</label>
                            <input :id="`${props.form.id}_variant_${index}_sku`" type="string"
                                :name="`${props.form.name}[variants][${index}][sku]`" :value="variant.sku"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                        </div>
                    </div>

                    <div class="flex space-x-2 mb-4">
                        <PriceInput
                            :form="{ id: `${props.form.id}_variant_${index}_price`, name: `${props.form.name}[variants][${index}][price]` }"
                            :data="variant.price" />
                    </div>

                    <div class="flex space-x-2 mb-4">
                        <DimensionInput
                            :form="{ id: `${props.form.id}_variant_${index}_dimensions`, name: `${props.form.name}[variants][${index}][dimensions]` }"
                            :data="variant.dimensions" />
                    </div>

                    <div>
                        <label :for="`${props.form.id}_variant_${index}_stock`"
                            class="block text-gray-700 text-sm font-bold mb-2">Stock</label>
                        <input :id="`${props.form.id}_variant_${index}_stock`" type="number"
                            :name="`${props.form.name}[variants][${index}][stock]`" :value="variant.stock" />
                    </div>
                </template>
            </Accordion>
        </template>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue';
import Accordion from './Accordion.vue';
import DimensionInput from './DimensionInput.vue';
import ImageGallery from './ImageGallery.vue';
import PriceInput from './PriceInput.vue';
import TagInput from './TagInput.vue';

const props = defineProps<{
    form: {
        id: string,
        name: string
    },
    options: Array<{
        name: string,
        values: Array<string>
    }>,
    variants: Array<{
        id: number,
        name: string,
        sku?: string,
        status: string,
        price: {
            currency?: string,
            normal?: number,
            sale?: number,
            date?: string
        },
        stock?: number,
        dimensions: {
            length?: number,
            width?: number,
            height?: number,
            weight?: number
        },
        image?: {
            key: Number;
            id: Number;
            name: string;
            mime: string;
            url: string;
        }
    }>
}>()

const optionContainer = ref<InstanceType<typeof HTMLDivElement>>()
let options = reactive(props.options)
let variants = reactive(props.variants)

function addOption() {
    if (options.length >= 2) return

    let optionName = prompt('Please enter the option name (Color, Volume etc.)')
    if (optionName == null) return
    else if (options.filter(option => option.name === optionName).length > 0) {
        alert(`The option ${optionName} is already included.`)
        return
    }

    options.push({
        name: optionName,
        values: []
    })
}

function computeOptionList(values: Array<string>) {
    let list: Array<{name: string, value: string}> = []
    values.forEach((value) => list.push({name: value, value: value}))
console.log(list)
    return list
}

function removeOption(index: number) {
    console.log(`remove option`)
    options.splice(index, 1)
    console.log(options)
}

function calculateVariants(changed: { key: string, value: string }) {
    options.forEach((option, index) => {
        if (option.name == changed.key) {
            options[index].values.splice(0)
            changed.value.split(`,`).forEach(element =>
                options[index].values.push(element)
            )
        }
    });

    let variantOptions = options.length > 1 ? options[0].values.flatMap(d => options[1].values.map(v => d + `, ` + v)) : options[0].values
    let existingVariantOptions = variants.map((variant) => variant.name)

    let toAdd = variantOptions.filter(x => !existingVariantOptions.includes(x));
    toAdd.forEach((variant) => variants.push({ id: 0, name: variant, status: `active`, price: {}, dimensions: {} }))

    let toRemove = existingVariantOptions.filter(x => !variantOptions.includes(x));
    toRemove.forEach((name) => variants.forEach((variant, index) => { if (variant.name == name) variants.splice(index, 1) }))

    console.log(variants.map((variant) => variant.name))
}

</script>
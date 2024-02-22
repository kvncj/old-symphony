

<template>
    <div class="flex space-x-2">
        <div class="price-input mb-4">
            <label :for="`${props.form.name}_normal`" class="block text-gray-700 text-sm font-bold mb-2">Regular
                Price</label>
            <div class="relative mt-1 rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 flex items-center">
                    <select id="price-input_currency" :name="`${props.form.name}[currency]`" @change=updateCurrency
                        class="h-full rounded-md border-transparent bg-transparent py-0 pl-2 pr-1 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option :selected="props.data.currency == 'MYR'">MYR</option>
                        <option :selected="props.data.currency == 'SGD'">SGD</option>
                    </select>
                </div>
                <input :id="`${props.form.name}_normal`" type="number" :name="`${props.form.name}[normal]`" v-model="normal"
                    class="shadow appearance-none border rounded w-full py-2 px-3 pl-16 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="0.00">
            </div>
        </div>

        <div class="mb-4">
            <label :for="`${props.form.name}_sale`" class="block text-gray-700 text-sm font-bold mb-2">Sale
                Price</label>
            <div class="relative mt-1 rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 flex items-center">
                    <select id="price-input_currency" :name="`${props.form.name}[currency]`" @change=updateCurrency
                        class="h-full rounded-md border-transparent bg-transparent py-0 pl-2 pr-1 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option :selected="props.data.currency == 'MYR'">MYR</option>
                        <option :selected="props.data.currency == 'SGD'">SGD</option>
                    </select>
                </div>
                <input :id="`${props.form.name}_sale`" type="number" :name="`${props.form.name}[sale]`" v-model="sale"
                    class="shadow appearance-none border rounded w-full py-2 px-3 pl-16 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="0.00">
            </div>
        </div>

        <div class="mb-4">
            <label :for="`${props.form.name}_date`" class="block text-gray-700 text-sm font-bold mb-2">Sale Date</label>
            <div class="relative mt-1 rounded-md shadow-sm">
                <input :id="`${props.form.name}_date`" type="text" :name="`${props.form.name}[date]`" ref="rangeInput"
                    v-model="date"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
        </div>
        
    </div>
</template>

<script lang="ts" setup>

import flatpickr from "flatpickr";
import { onMounted, ref } from 'vue';

const props = defineProps<{
    form: {
        id: string,
        name: string
    },
    data: {
        currency?: string,
        normal?: number,
        sale?: number,
        date?: string
    }
}>()

const rangeInput = ref<InstanceType<typeof HTMLElement>>()

let currency =  ref(props.data.currency)
let normal = ref(props.data.normal)
let sale = ref(props.data.sale)
let date = ref(props.data.date)

function updateCurrency(ev: Event) {
    console.log(`looping`)
    let selection = (<HTMLSelectElement>ev.currentTarget).value
    let currencyInputs: NodeListOf<HTMLSelectElement> = document.querySelectorAll(`#price-input_currency`)
    currencyInputs.forEach((element) => element.value = selection)
}

onMounted(() => {
    flatpickr(<HTMLElement>rangeInput.value, {
        mode: "range",
        dateFormat: "Y-m-d",
    })
})


</script>
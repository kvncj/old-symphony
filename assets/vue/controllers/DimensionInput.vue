

<template>
    <div class="flex space-x-2">
        <div class="mb-4">
            <label :for="`${props.form.name}_length`" class="block text-gray-700 text-sm font-bold mb-2">
                Length (cm)
            </label>
            <input type="number"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                :id="`${props.form.name}_length`" :name="`${props.form.name}[length]`" v-model="length">
        </div>
        <div class="mb-4">
            <label :for="`${props.form.name}_width`" class="block text-gray-700 text-sm font-bold mb-2">
                Width (cm)
            </label>
            <input type="number"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                :id="`${props.form.name}_width`" :name="`${props.form.name}[width]`" v-model="width">
        </div>
        <div class="mb-4">
            <label :for="`${props.form.name}_height`" class="block text-gray-700 text-sm font-bold mb-2">
                Height (cm)
            </label>
            <input type="number"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                :id="`${props.form.name}_height`" :name="`${props.form.name}[height]`" v-model="height">
        </div>
        <div class="mb-4">
            <label :for="`${props.form.name}_weight`" class="block text-gray-700 text-sm font-bold mb-2">
                Weight (kg)
            </label>
            <input type="number"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                :id="`${props.form.name}_weight`" :name="`${props.form.name}[weight]`" v-model="weight">
        </div>
    </div>
</template>

<script lang="ts" setup>

import { ref } from 'vue';

const props = defineProps<{
    form: {
        id: string,
        name: string
    },
    data: {
        length?: number,
        width?: number,
        height?: number,
        weight?: number
    }
}>()

let length = ref(props.data.length)
let width = ref(props.data.width)
let height = ref(props.data.height)
let weight = ref(props.data.weight)

</script>
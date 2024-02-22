<template>
    <div class="flex">
        <nav class="navbar bg-light">
            <ul class="">
                <li v-for="label, index in labels" class="">
                    <a class="relative flex flex-row items-center h-11 focus:outline-none hover:bg-gray-50 text-gray-600 hover:text-gray-800 border-l-4 hover:border-indigo-500 pl-2 pr-6"
                    :class="{ 'bg-gray-50 border-indigo-500 text-gray-800': activePage==index }"
                        role="button" @click="changePage(index)">
                        <span class="ms-1 d-none d-sm-inline"></span>
                        {{ capitalize(label) }}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="container mb-3 g-2">
            <div v-for="content, index in contents" v-show="activePage==index">
                <p v-html="content"></p>
            </div>
        </div>
    </div>
</template>


<script lang="ts" setup>

import { ref } from 'vue';

const props = defineProps<{
    labels: Array<string>,
    contents: Array<any>
}>()

let activePage = ref(0)

function capitalize(word: string) {
    return word.charAt(0).toUpperCase() + word.slice(1)
}

function changePage(index: number) {
    activePage.value = index
    console.log(activePage)
}

</script>
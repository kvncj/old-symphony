<template>
    <div class="accordion">
        <header @click="toggle" class="flex px-4 py-2 border-b mb-4 bg-slate-200 cursor-pointer">
            <div class="flex-auto">
                <slot name="header"></slot>
            </div>
            <ChevronUp class="flex-none" v-if="isOpen"></ChevronUp>
            <ChevronDown class="flex-none" v-if="!isOpen"></ChevronDown>
        </header>
        <main v-show="isOpen">
            <slot></slot>
        </main>
        <footer v-show="isOpen" class="space-x-2 mb-4">
            <slot name="footer"></slot>
        </footer>
    </div>
</template>

<script lang="ts" setup>

import { ref } from 'vue';
import ChevronDown from './icon/ChevronDown.vue';
import ChevronUp from './icon/ChevronUp.vue';

let isOpen = ref(false)
function toggle() {
    isOpen.value = !isOpen.value;
}

</script>
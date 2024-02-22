

<template>
    <div class="password-field">
        <label v-if="props.label" for={{id}} :class=vars.labelClass>{{ props.label }}</label>
        <div class="password-field__wrapper">
            <input :id=props.id :name=props.name class="password-field__input" :class=vars.class.value
                :type=vars.type.value :placeholder=props.placeholder>
            <i @click="toggle" class="password-field__toggle">
                <EyeSvg v-if="state.visible" />
                <EyeSlashSvg v-if="!state.visible" />
            </i>
        </div>
    </div>
</template>

<script lang="ts" setup>

import { computed, reactive } from 'vue';
import EyeSlashSvg from './icon/EyeSlashSvg.vue';
import EyeSvg from './icon/EyeSvg.vue';

const props = defineProps({
    id: { type: String, required: true },
    name: { type: String, required: true },
    placeholder: { type: String, required: false },
    class: { type: String, required: false },
    errorClass: { type: String, required: false },
    label: { type: String, required: false },
    labelClass: { type: String, required: false }
})

let state = reactive({
    error: false,
    visible: false
})

let vars = {
    class: computed(() => (props.class ?? "shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline").concat(state.error ? props.errorClass ?? " border-red-500" : '')),
    labelClass: props.labelClass ?? "block text-gray-700 text-sm font-bold mb-2",
    type: computed(() => state.visible ? 'text' : 'password')
}

let toggle = function (e: Event) {
    state.visible = !state.visible
    console.log(state.visible)
}

</script>

<style lang="scss">
.password-field {
    margin-bottom: 10px;

    .password-field__wrapper {
        position: relative;

        .password-field__input {
            margin-bottom: 0px;
            padding-right: 25px;
        }

        .password-field__toggle {
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            width: 15px;
            height: fit-content;
            margin: auto;
            margin-right: 5px;
            color: lightgray;
            cursor: pointer;
        }
    }
}
</style>
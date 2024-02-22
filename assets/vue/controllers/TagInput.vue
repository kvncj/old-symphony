

<template>
    <input ref="input" type="text" :name="`${props.form.name}`" :value="props.form.value.value" :class="props.form.class" />
    <input v-model="name" type="hidden" :name="`${props.form.name}[name]`" :class="props.form.class" />
    <input v-model="value" type="hidden" :name="`${props.form.name}[value]`" :class="props.form.class" />
</template>

<script lang="ts" setup>

import TomSelect from 'tom-select';
import { onMounted, ref } from 'vue';

const emit = defineEmits([`add`, 'change', `remove`])
const props = defineProps<{
    form: {
        key2: any,
        name: string,
        value: { 
            name:string,
            value:string
        }
        class?: string,
    }
    settings: {
        create?: boolean
        loadData?: boolean
        loadDataURL?: string
        loadDataParam?: string
        maxItems?: number,
        options?: Array<any>
    }
}>()


const input = ref<InstanceType<typeof HTMLElement>>()

let name = ref(props.form.value.name)
let value = ref(props.form.value.value)

let tomSelect: TomSelect | null = null

onMounted(() => {
    // @ts-ignore
    tomSelect = new TomSelect(input.value, {
        create: props.settings.create ?? true,
        createFilter: function (input) {
            input = input.toLowerCase();
            let pattern = this.input.dataset.tsvalidate
            if (null !== pattern) {
                let regex = new RegExp(pattern);
                if (!input.match(regex))
                    return false
            }
            return !(input in this.options);
        },
        highlight: false,
        maxItems: props.settings.maxItems ?? 10,
        maxOptions: 100,
        options: props.settings.options ?? [],
        onChange: onChange.bind(this),
        valueField: 'value',
        labelField: 'name',
        searchField: 'name',
    })

    if (props.settings.loadData == true) {
        tomSelect.settings.shouldLoad = function (str) {
            return str.length > 1
        }
        tomSelect.load = function (str) {
            let loadURL = new URL(<string>props.settings.loadDataURL)
            loadURL.searchParams.set(props.settings.loadDataParam ?? 'query', str)
            fetch(loadURL)
                .then(response => response.json())
                .then(response => {
                    tomSelect = <TomSelect>tomSelect
                    tomSelect.clearOptions()
                    tomSelect.addOptions(response.data, false)
                    tomSelect.refreshOptions()
                })
        }
    }
})

function onChange(selected: string) {
    let option = (<TomSelect>tomSelect).options[selected]
    name.value = option.name
    value.value = option.value
    emit('change', {
        key: props.form.key2,
        value: selected
    })
}

</script>
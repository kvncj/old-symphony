<template>
    <div class="fixed flex flex-col top-0 left-0 w-[200px] bg-white h-full border-r antialiased bg-gray-50 text-gray-800">
        <div class="flex items-center justify-center h-14 border-b">
            <div>{{ props.label }}</div>
        </div>
        <div class="overflow-y-auto overflow-x-hidden flex-grow">
            <ul class="flex flex-col py-4 space-y-1">
                <template v-for="section in props.sections">
                    <li v-if=section.label class="px-5">
                        <div class="flex flex-row items-center h-8">
                            <div class="text-sm font-light tracking-wide text-gray-500">{{ section.label }}</div>
                        </div>
                    </li>
                    <template v-for="item in section.items">
                        <li>
                            <a :href=item.link
                                class="relative flex flex-row items-center h-11 focus:outline-none hover:bg-gray-50 text-gray-600 hover:text-gray-800 border-l-4 border-transparent hover:border-indigo-500 pr-6">
                                <span class="inline-flex justify-center items-center ml-4">
                                    <p v-html="item.icon"></p>
                                </span>
                                <span class="ml-2 text-sm tracking-wide truncate">{{ item.label }}</span>
                            </a>
                        </li>
                    </template>
                    <hr class="my-2">
                </template>
            </ul>
        </div>
    </div>
</template>

<script lang="ts" setup>

interface Section {
    label?: string;
    items: Array<SectionItem>;
}

interface SectionItem {
    label: string;
    icon: string;
    active: boolean;
    link: string;
}

const props = defineProps({
    label: { type: String, required: false },
    sections: { type: Array<Section>, required: true }
})

</script>
<template>
    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
        <ul class="pagination flex">
            <li class="page-item">
                <button class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20 hover:cursor-pointer"
                    :data-page="props.currentPage - 1"
                    :disabled="props.currentPage == 1 ? true : false">Previous</button>
            </li>

            <li class="page-item">
                <button class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20 hover:cursor-pointer"
                    :class="props.currentPage == 1 ? 'border-indigo-500 bg-indigo-50 text-indigo-600' : ''"
                    @click="switchPage" data-page="1" :disabled="props.currentPage == 1 ? true : false">{{ 1 }}</button>
            </li>

            <li v-if="shownPageNumbers[0] > 2">
                <button class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20 hover:cursor-pointer"
                    @click="switchPage">...</button>
            </li>

            <template v-for="i in shownPageNumbers">
                <li v-if="i > 1 && i < totalPages" class="page-item">
                    <button class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20 hover:cursor-pointer"
                        :class="props.currentPage == i ? 'border-indigo-500 bg-indigo-50 text-indigo-600' : ''"
                        @click="switchPage" :data-page="i" :disabled="props.currentPage == i">{{ i }}</button>
                </li>
            </template>

            <li v-if="props.totalPages > shownPageNumbers[4] && (props.totalPages - shownPageNumbers[4]) > 1">
                <button class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20 hover:cursor-pointer"
                    @click="switchPage">...</button>
            </li>

            <li v-if="props.totalPages > shownPageNumbers[4]" class="page-item">
                <button class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20 hover:cursor-pointer"
                    :class="props.currentPage == props.totalPages ? 'border-indigo-500 bg-indigo-50 text-indigo-600' : ''"
                    @click="switchPage" :data-page="props.totalPages"
                    :disabled="props.currentPage == props.totalPages">{{ props.totalPages
                    }}</button>
            </li>

            <li class="page-item">
                <button class="relative inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-20 hover:cursor-pointer"
                    @click="switchPage" :data-page="props.currentPage + 1" :disabled="props.currentPage == props.totalPages">Next</button>
            </li>
        </ul>
    </nav>

</template>

<script lang="ts" setup>

const props = defineProps({
    currentPage: { type: Number, required: true },
    totalPages: { type: Number, required: true }
})

let shownPageNumbers = [props.currentPage - 2, props.currentPage - 1, props.currentPage, props.currentPage + 1, props.currentPage + 2]

function switchPage(ev: Event) {
    let pageItem = <HTMLElement>ev.currentTarget
    let page = pageItem.dataset.page ?? `0`;
    if (page == `0`) {
        console.log(`prompt`)
        page = prompt(`Jump to page: `) ?? `1`;
    }

    let url = new URL(window.location.href);
    url.searchParams.set(`page`, page);

    window.location.href = url.toString()
}

</script>
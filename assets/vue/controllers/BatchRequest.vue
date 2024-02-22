

<template>
    <div>
        <div>GO GO GO!</div>
        <button @click="start">Dispatch</button>
    </div>
</template>

<script lang="ts" setup>

const props = defineProps<{
    url: string,
    param: string,
    calls: number
}>()

function start() {
    dispatch();
}

function dispatch(loop: number = 0) {
    console.log(`Dispatch loop ${loop}`)
    if (loop < props.calls) {
        let url = new URL(props.url)
        url.searchParams.set(props.param, loop.toString())

        fetch(url)
            .then(response => response.json())
            .then(body => {
                console.log(body)
                if (`success` == body.status || `warning` == body.status) {
                    if (`warning` == body.status) {
                        body.errors.forEach((str) => console.log(str))
                    }
                    dispatch(++loop)
                }
            })
            .catch(error => {
                console.log(error)
                retry(url)
            })
    } else {
        alert('Complete')
    }
}

function retry(url: URL, attempt: number = 1) {
    fetch(url)
        .then(response => response.json())
        .then(body => {
            if (`success` != body.status)
                throw body.data
        })
        .catch(error => {
            if (attempt > 2)
                console.log(`Error processing batch: Connection error. Please process it <a href="${url}">manually</a>.`)
            else retry(url, ++attempt)
        })
}
</script>
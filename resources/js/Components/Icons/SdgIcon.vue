<script lang="ts" setup>
import { computed } from "vue";
const props = defineProps({
    name: String
})

const icon = computed(() => {
    if (props.name == 'sdg-1') {
        return 'bg-sdg-1'
    } else if (props.name == 'sdg-2') {
        return 'bg-sdg-2'
    } else if (props.name == 'sdg-3') {
        return 'bg-sdg-3'
    } else if (props.name == 'sdg-4') {
        return 'bg-sdg-4'
    } else if (props.name == 'sdg-5') {
        return 'bg-sdg-5'
    } else if (props.name == 'sdg-6') {
        return 'bg-sdg-6'
    } else if (props.name == 'sdg-7') {
        return 'bg-sdg-7'
    } else if (props.name == 'sdg-8') {
        return 'bg-sdg-8'
    } else if (props.name == 'sdg-9') {
        return 'bg-sdg-9'
    } else if (props.name == 'sdg-10') {
        return 'bg-sdg-10'
    } else if (props.name == 'sdg-11') {
        return 'bg-sdg-11'
    } else if (props.name == 'sdg-12') {
        return 'bg-sdg-12'
    } else if (props.name == 'sdg-13') {
        return 'bg-sdg-13'
    } else if (props.name == 'sdg-14') {
        return 'bg-sdg-14'
    } else if (props.name == 'sdg-15') {
        return 'bg-sdg-15'
    } else if (props.name == 'sdg-16') {
        return 'bg-sdg-16'
    } else if (props.name == 'sdg-17') {
        return 'bg-sdg-17'
    } else if (props.name == 'sdg-18') {
        return 'bg-sdg-18'
    }
})
</script>
<template>
    <div class="bg-sdg h-sdg w-sdg flex-none" :class="icon"></div>
</template>

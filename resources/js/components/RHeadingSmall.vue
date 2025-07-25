<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <header>
        <h3 v-if="description" class="text-sm text-muted-foreground">
            {{ title }}
        </h3>
        <p class="mb-0.5 text-base font-normal">{{ description }}</p>        
    </header>
</template>

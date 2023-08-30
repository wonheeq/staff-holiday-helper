<template>
    <div class="grid place-items-center fixed inset-0 backdrop-blur-sm bg-black/60 h-full w-full overflow">
        <slot />
    </div>
</template>
<style>
.overflow {
    overflow: auto;
}
</style>
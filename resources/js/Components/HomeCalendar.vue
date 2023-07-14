<script setup>
import { Calendar, DatePicker } from 'v-calendar';
import 'v-calendar/style.css';

let date = new Date();
</script>

<template>
<div class="bg-white rounded-md">
    <Calendar :rows="3" borderless expanded transparent class="max-h-full">

    </Calendar>
</div>
</template>
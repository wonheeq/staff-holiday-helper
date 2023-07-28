<script setup>
let props = defineProps({ nominations: Object });

const pClass = "text-sm";
</script>
<template>
<div>
    <p :class="pClass">
        Nominees Accepted: {{ nominations.filter(n => n.status === 'Y').length }}/{{ nominations.length }}
    </p>
    <p :class="pClass">
        Nominees Undecided: {{ nominations.filter(n => n.status === 'U').length  }}
    </p>
    <p :class="pClass">
        Nominees Rejected: {{ nominations.filter(n => n.status === 'N').length  }}
    </p>
</div>
</template>
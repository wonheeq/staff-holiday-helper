<script setup>
let props = defineProps({ nominations: Object, appStatus: String });

const pClass = "text-sm";
</script>
<template>
<div>
    <p :class="pClass">
        Nominees Accepted: {{ nominations.filter(n => n.status === 'Y').length }}/{{ nominations.length }}
    </p>
    <p v-if="appStatus === 'P'" :class="pClass">
        Nominees Undecided: {{ nominations.filter(n => n.status === 'U').length  }}
    </p>
    <p v-if="appStatus === 'P'" :class="pClass">
        Nominees Rejected: {{ nominations.filter(n => n.status === 'N').length  }}
    </p>
    <p v-if="appStatus === 'U'" :class="pClass">
        [Awaiting Line Manager Decision]
    </p>
</div>
</template>
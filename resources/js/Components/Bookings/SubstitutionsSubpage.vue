<script setup>
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
let deadAreaColor = "#FFFFFF";

let substitutions = [
    {
        task: "test",
        sDate: "2023-08-03 14:30",
        eDate: "2023-08-04 14:30",
        applicantName: "Test"
    },
    {
        task: "test",
        sDate: "2023-08-03 14:30",
        eDate: "2023-08-04 14:30",
        applicantName: "Test"
    },
    {
        task: "test",
        sDate: "2023-08-03 14:30",
        eDate: "2023-08-04 14:30",
        applicantName: "Test"
    },
];

</script>
<template>
    <div>
        <p>
            Substitutions subpage
        </p>

        <VueScrollingTable
            :deadAreaColor="deadAreaColor"
            :scrollHorizontal="false"
        >
            <template #tbody>
                <div v-for="item in substitutions" :key="item.id" class="mb-2">
                    <p>
                        {{ item.task }} for {{  item.applicantName }}
                    </p>
                    <p>
                        Duration: {{ item.sDate }} - {{ item.eDate }}
                    </p>
                </div>
            </template>
        </VueScrollingTable>
    </div>
</template>
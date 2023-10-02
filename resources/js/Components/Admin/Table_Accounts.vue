<script setup>

import 'vue-good-table-next/dist/vue-good-table-next.css';
import { VueGoodTable } from 'vue-good-table-next';

</script>

<script>
import axios from "axios";

export default {
    props: {
        user: {
            type: String,
            required: true
        }
    },
    data: function() {
        let defaultC = 364;
        return {
            columns: [
                {
                label: 'Account ID',
                field: 'accountNo',              
                },
                {
                label: 'Account Type',
                field: 'accountType',
                },
                {
                label: 'Surname',
                field: 'lName',
 
                },
                {
                label: 'First/Other Names',
                field: 'fName',
                },
                {
                label: 'School',
                field: 'schoolId',
                },
                {
                label: 'Line Manager',
                field: 'superiorNo',
                },
                {
                label: 'Created/Last Updated (UTC)',
                field: 'updated_at',
                }
            ],
            accounts: [],
            c: defaultC,
            tHeight: ((0.8889 * window.innerHeight) - defaultC).toFixed(0) + "px",    
            tStyle: "vgt-table",
            fontSize: "14px",
            searchPadding: '6px 12px',
            searchHeight: '32px',
            magnifyingGlassTop: '3px',
            magnifyingGlassLeft: '8px',
            magnifyingGlassWH: '16px',
            sortButtonBorder: '5px',
            sortButtonMargin: '-7px',
            pageChangeBorder: '6px',
            pageChangeMargin: '-6px',
            footerFontSize: '1.1rem',
            pageDropdownRight: '6px',
            pageDropdownMargin: '-1px'
        };
    },
    created() {
        //console.warn("/api/allAccounts/" + this.user)
        axios.get("/api/allAccounts/" + this.user)
        .then((response) => {
            this.accounts = response.data;
            //console.log(response.data);
        })
        .catch((error) => {
            console.log(error);
        });
    },
    // Using height of window to determine max table height
    mounted() {
        if (screen.width >= 3840) {
            this.tStyle = 'vgt-table scaled';
            this.fontSize = '1.8rem';
            this.searchPadding = '12px 18px';
            this.searchHeight = '54px';
            this.c = 468;
            this.magnifyingGlassTop = '7px';
            this.magnifyingGlassLeft = '5px';
            this.magnifyingGlassWH = '25px';
            this.sortButtonBorder = '8px';
            this.sortButtonMargin = '-13px';
            this.pageChangeBorder = '10px';
            this.pageChangeMargin = '-10px';
            this.footerFontSize = '1.8rem';
            this.pageDropdownRight = '-4px';
            this.pageDropdownMargin = '-3px';
            this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        }
        this.$nextTick(() => {
            window.addEventListener('resize', this.onResize);
            //console.warn("tHeight: ", this.tHeight)
        })
    },
    beforeDestroy() { 
        window.removeEventListener('resize', this.onResize); 
    },
    methods: {  
        onResize() {
        this.tHeight = ((0.8889 * window.innerHeight) - this.c).toFixed(0) + "px"
        //this.tHeight = (window.innerHeight).toFixed(0) + "px"
        //console.warn("tHeight: ", this.tHeight)
        },
    }
};

let onSearch = () => {
};
</script>


<template>
    <div class="parent1">
        <div class="mx-4 mt-4 4k:mt-8">
            <div remove-tailwind-bg>
                <VueGoodTable
                    :rows="accounts"
                    :columns="columns"
                    v-bind:styleClass= tStyle
                    v-bind:max-height= tHeight
                    :fixed-header="{
                        enabled: true,
                    }"
                    :search-options="{
                        enabled: true,
                        placeholder: 'Search Staff Accounts',
                    }"
                    :pagination-options="{
                        enabled: true,
                        //mode: 'pages',
                        perPage: 30
                    }">
                    <template #emptystate>
                        No entries found!
                    </template>     
                </VueGoodTable> 
            </div>           
       </div>
    </div>
</template>

<style>
    .parent1 {
        width: 100%;       
    }
   
    .scaled {
        font-size: 1.8rem !important;
    }

    .vgt-input, .vgt-select {
        font-size: v-bind(fontSize) !important;
        padding:  v-bind(searchPadding) !important;
        height: v-bind(searchHeight) !important;
    }

    .vgt-global-search__input .input__icon .magnifying-glass {
        margin-top: v-bind(magnifyingGlassTop) !important;
        margin-left: v-bind(magnifyingGlassLeft) !important;
        width: v-bind(magnifyingGlassWH) !important;
        height: v-bind(magnifyingGlassWH) !important;
    }

    .vgt-table th.sortable button:before {
        margin-bottom: v-bind(sortButtonMargin);
        border-left: v-bind(sortButtonBorder) solid transparent;
        border-right: v-bind(sortButtonBorder) solid transparent;
        border-top: v-bind(sortButtonBorder) solid #606266;
    }
    .vgt-table th.sortable button:after {
        margin-top: v-bind(sortButtonMargin);
        border-left: v-bind(sortButtonBorder) solid transparent;
        border-right: v-bind(sortButtonBorder) solid transparent;
        border-bottom: v-bind(sortButtonBorder) solid #606266;
    }

    .vgt-table thead th.sorting-asc button:after {
        border-bottom: v-bind(sortButtonBorder) solid #409eff;
    }
    .vgt-table thead th.sorting-desc button:before {
        border-top: v-bind(sortButtonBorder) solid #409eff;
    }

    .vgt-wrap__footer .footer__row-count__label,
    .vgt-wrap__footer .footer__row-count__select,
    .vgt-wrap__footer .footer__navigation,
    .vgt-wrap__footer .footer__navigation__page-btn span {
        font-size: v-bind(footerFontSize);
    }

    .vgt-wrap__footer .footer__navigation__page-btn .chevron.left::after {
        border-right: v-bind(pageChangeBorder) solid #409eff;
    }

    .vgt-wrap__footer .footer__navigation__page-btn .chevron.right::after {
        border-left: v-bind(pageChangeBorder) solid #409eff;
    }

    .vgt-wrap__footer .footer__navigation__page-btn .chevron:after {
        margin-top: v-bind(pageChangeMargin);
        border-top: v-bind(pageChangeBorder) solid transparent;
        border-bottom: v-bind(pageChangeBorder) solid transparent;
    }

    .vgt-wrap__footer .footer__row-count::after {
        right: v-bind(pageDropdownRight);
        margin-top: v-bind(pageDropdownMargin);
        border-top: v-bind(pageChangeBorder) solid #606266;
        border-left: v-bind(pageChangeBorder) solid transparent;
        border-right: v-bind(pageChangeBorder) solid transparent;
    }
</style>
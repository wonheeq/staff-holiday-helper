import { defineStore } from 'pinia';

export let useDataFieldsStore = defineStore('fields', {
    state: () => ({
        accountFields: [ 
            {desc:"Account Number (Staff ID)",attr:"accountNo",plhldr:"e.g. 000000a",fk:"none",fkAttr:""},
            {desc:"Account Type",attr:"accountType",plhldr:"e.g. Staff",fk:"acctTypes",fkAttr:"name"},
            {desc:"Surname",attr:"lName",plhldr:"Fill",fk:"none",fkAttr:""},
            {desc:"First/Other Names",attr:"fName",plhldr:"Fill",fk:"none",fkAttr:""},
            {desc:"School Name",attr:"school",plhldr:"e.g. Curtin Law School",fk:"schools",fkAttr:"name"},
            {desc:"Line Manager's ID",attr:"superiorNo",plhldr:"Select",fk:"lmanagers",fkAttr:"fullName"}
        ],
        applicationFields: [
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""}   
        ],
        nominationFields: [
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
        ],
        accountRoleFields: [
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
        ],
        roleFields: [
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
        ],
        unitFields: [
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
        ],
        majorFields: [
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
        ],
        courseFields: [
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
        ],
        schoolFields: [
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
        ],
        messageFields: [
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
            {desc:"",attr:"",plhldr:"",fk:"none",fkAttr:""},
        ],
    }),

    getters: {
        getFields() 
        {
            return this.accountFields;
        }
    }
});
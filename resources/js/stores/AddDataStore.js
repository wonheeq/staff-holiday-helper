import { defineStore } from 'pinia';

export let useDataFieldsStore = defineStore('fields', {
    state: () => ({
        accountFields: [ 
            {desc:"Account Number",attr:"accountNo",plhldr:"e.g. 000000a",fk:"none",fkAttr:""},
            {desc:"Account Type",attr:"accountType",plhldr:"e.g. Staff",fk:"acctTypes",fkAttr:"name"},
            {desc:"Surname",attr:"lName",plhldr:"Fill",fk:"none",fkAttr:""},
            {desc:"First/Other Names",attr:"fName",plhldr:"Fill",fk:"none",fkAttr:""},
            {desc:"School Name",attr:"school",plhldr:"Select School",fk:"schools",fkAttr:"name"},
            {desc:"Line Manager's ID",attr:"superiorNo",plhldr:"Select Account or \"None\"",fk:"lmanagers",fkAttr:"fullName"}
        ],
        accountRoleFields: [
            {desc:"Account Number",attr:"accountNo",plhldr:"Select",fk:"displayAccounts",fkAttr:"fullName"},
            {desc:"Role",attr:"roleId",plhldr:"e.g. Unit Coordinator",fk:"roles",fkAttr:"name"},
            {desc:"Unit",attr:"unitId",plhldr:"Select Unit or \"None\"",fk:"units",fkAttr:"disName"},
            {desc:"Major",attr:"majorId",plhldr:"Select Major or \"None\"",fk:"majors",fkAttr:"disName"},
            {desc:"Course",attr:"courseId",plhldr:"Select Course or \"None\"",fk:"courses",fkAttr:"disName"},
            {desc:"School",attr:"schoolId",plhldr:"Select School",fk:"schools",fkAttr:"name"}
        ],
        roleFields: [
            {desc:"Role Name",attr:"name",plhldr:"New Role Name",fk:"none",fkAttr:""}
        ],
        unitFields: [
            {desc:"Unit Name",attr:"name",plhldr:"New Unit Name",fk:"none",fkAttr:""},
            {desc:"Unit Code",attr:"unitId",plhldr:"e.g. ABCD1234",fk:"none",fkAttr:""}
        ],
        majorFields: [
            {desc:"Major Name",attr:"name",plhldr:"New Major Name",fk:"none",fkAttr:""},
            {desc:"Major Code",attr:"majorId",plhldr:"e.g. MJRU-SFTEN",fk:"none",fkAttr:""}
        ],
        courseFields: [
            {desc:"Course Name",attr:"name",plhldr:"New Course Name",fk:"none",fkAttr:""},
            {desc:"Course Code",attr:"courseId",plhldr:"e.g. B-COMP",fk:"none",fkAttr:""}
        ],
        schoolFields: [
            {desc:"School Name",attr:"name",plhldr:"New School Name",fk:"none",fkAttr:""}
        ],
    }),

    getters: {
        getFields() 
        {
            return this.accountFields;
        }
    }
});
<script>
import {scroller} from 'vue-scrollto/src/scrollTo'
import { dateInputMask, moneyInputMask, parseMoney, moneyInputMaskAll, flashErrors }  from "../../helper.js";
export default {
  props: [
    'contributions',
    'datesGlobal',
    'datesContributions',
    'datesItemZeroWithContribution',
    'datesItemZeroWithoutContribution',
    'datesSecurityBattalionWithContribution',
    'datesSecurityBattalionWithoutContribution',
    'datesMay1976WithoutContribution',
    'datesCertificationPeriodWithContribution',
    'datesCertificationPeriodWithoutContribution',
    'datesNotWorked',
    'datesAvailability',
    "retirementFundId",
    'affiliate'
  ],
  mounted() {
    // moneyInputMaskAll();
    this.addGuarantor();
  },
  data() {
    return {


      years: this.contributions.years,
      months: this.contributions.months,
      yearsContributions: 0,
      monthsContributions: 0,
      yearsGlobal: 0,
      monthsGlobal: 0,
      yearsAvailability: 0,
      monthsAvailability: 0,
      yearsItemZero: 0,
      monthsItemZero: 0,
      yearsSecurityBattalion:0,
      monthsSecurityBattalion:0,
      yearsNoRecords:0,
      monthsNoRecords:0,
      yearsCas:0,
      monthsCas:0,

      showEconomicData:false,
      showEconomicDataTotal:false,
      showPercentagesRetFun:false,
      showPercentagesRetFunAvailability:false,

      subTotalRetFun: 0,
      totalRetFun: 0,

      advancePayment: 0,
      advancePaymentNoteCodeDate: null,
      advancePaymentNoteCode: null,
      advancePaymentCode: null,
      advancePaymentDate: null,
      retentionLoanPayment: 0,
      retentionLoanPaymentNoteCodeDate:null,
      retentionLoanPaymentNoteCode:null,
      retentionLoanPaymentCode:null,
      retentionLoanPaymentDate:null,
      retentionGuarantor: 0,
      retentionGuarantorNoteCodeDate:null,
      retentionGuarantorNoteCode:null,
      retentionGuarantorCode:null,
      retentionGuarantorDate:null,

      hasAvailability: false,
      finishRetFun: false,
      finishAvailability: false,

      arrayDiscounts: [],

      subTotalAvailability:0,
      totalAnnualYield:0,
      totalAvailability:0,
      total:0,

      beneficiaries: [],
      beneficiariesAvailability: [],
      beneficiariesRetFunAvailability: [],

      // perecentageAdvancePayment: 0,
      totalAverageSalaryQuotable: 0,
      totalQuotes:0,
      guarantors:[],

      maxPercentage: 100.00,
      serviceYears:this.affiliate.service_years,
      serviceMonths:this.affiliate.service_months,

    };
  },
  methods: {
    updateTotalGuarantor(){
      this.retentionGuarantor = this.guarantors.reduce((acc, g)=>{
        return acc + parseFloat(parseMoney(g.amount));
      },0);
      if(this.retentionGuarantor == 0){
        this.retentionGuarantorCode = null;
        this.retentionGuarantorNoteCode = null;
        this.retentionGuarantorNoteCodeDate = null;
        this.retentionGuarantorDate = null;
      }
    },
    addGuarantor(){
      let guarantor = {
        amount: 0,
        identity_card: null,
        full_name: null,
        id: null
      }
      if (this.guarantors.length > 0) {
        if (! this.guarantors.some( g =>  { return !g.full_name || !g.identity_card })) {
          this.guarantors.push(guarantor);
        }
      }else{
        this.guarantors.push(guarantor);
      }
      setTimeout(() => {
        moneyInputMaskAll();
        if(this.$refs.guarantoridentitycard){
          let lastIndex = this.$refs.guarantoridentitycard.length-1;
          this.$refs.guarantoridentitycard[lastIndex].focus();
        }
      }, 500);
      this.updateTotalGuarantor();
    },
    deleteGuarantor(index){
      this.guarantors.splice(index,1);
      if(this.guarantors.length < 1)
        // this.addGuarantor();
      this.updateTotalGuarantor();
    },
    searchGuarantor(index){
      let ci = this.guarantors[index].identity_card;
      axios.get('/search_ajax', {
        params: {
          ci
        } 
      })
      .then( (response) => {
        let data = response.data;
        if (data.type == 'afiliado') {
          this.guarantors[index].full_name = `${data.first_name} ${data.second_name} ${data.last_name} ${data.mothers_last_name} ${data.surname_husband}`;
          this.guarantors[index].id = data.id;
          // if(data.first_name){
          //   let lastIndex = this.$refs.guarantoramount.length-1;
          //   this.$refs.guarantoramount[lastIndex].focus();

          // }

        }
      })
      .catch(function (error) {
        console.log('error al buscar garante: ', error);
      });
    },
    calculateDiff(dates){
      const diff = dates.reduce((prev, current)=>{
            return prev + (moment(current.end).diff(moment(current.start), 'months') + 1);
        }, 0);
      return diff;
    },
    calculateDiffWithYearMonth(dates){
      let date = this.calculateDiff(dates);
      return{
        years: parseInt(date/12),
        months: (date%12),
      }
    },
    async validateBeforeSubmit() {
      try {
          await this.$validator.validateAll();
      } catch (error) {
          console.log("some error");
      }
    },
    validAll(){
      return Object.keys(this.$validator.errors.collect()).length > 0;
    },
    firstContinue(){
      this.validateBeforeSubmit();
      if (this.validAll() === true) {
        flash("Debe completar el formulario", 'error')
        return;
      }
      let uri = `/ret_fun/${this.retirementFundId}/get_average_quotable`;
      axios.get(uri, {params:
        {
          service_years: this.serviceYears,
          service_months: this.serviceMonths,
        }}
      ).then(response =>{
          flash("Verificacion Correcta");
          this.showEconomicData = true
          TweenLite.to(this.$data, 0.5, { totalAverageSalaryQuotable: response.data.total_salary_quotable.total_average_salary_quotable,totalQuotes: response.data.total_quotes });
          setTimeout(() => {
            this.$scrollTo('#showEconomicData');
          }, 800);
      }).catch(error =>{
          flashErrors('Error al procesar los datos: ',error.response.data.errors)
          this.showEconomicData = false;
      });
    },
    saveAverageQuotable(){
      let uri=`/ret_fun/${this.retirementFundId}/save_average_quotable`;
      axios.get(uri).then(response => {
        flash("Salario Promedio Cotizable Actualizado")
        this.showEconomicDataTotal = true;
        if (response.data.discounts.length) {
          let advancePaymentResponse = response.data.discounts.filter(d => d.id == 1);
          if (advancePaymentResponse.length) {
            this.advancePayment = advancePaymentResponse[0].pivot.amount;
            this.advancePaymentNoteCodeDate = advancePaymentResponse[0].pivot.note_code_date;
            this.advancePaymentNoteCode = advancePaymentResponse[0].pivot.note_code;
            this.advancePaymentCode = advancePaymentResponse[0].pivot.code;
            this.advancePaymentDate = advancePaymentResponse[0].pivot.date;
          }

          let retentionLoanPaymentResponse = response.data.discounts.filter(d => d.id == 2);                    
          if (retentionLoanPaymentResponse.length) {
            this.retentionLoanPayment = retentionLoanPaymentResponse[0].pivot.amount;
            this.retentionLoanPaymentNoteCodeDate = retentionLoanPaymentResponse[0].pivot.note_code_date;
            this.retentionLoanPaymentNoteCode = retentionLoanPaymentResponse[0].pivot.note_code;
            this.retentionLoanPaymentCode = retentionLoanPaymentResponse[0].pivot.code;
            this.retentionLoanPaymentDate = retentionLoanPaymentResponse[0].pivot.date;
          }

          let retentionGuarantorResponse = response.data.discounts.filter(d => d.id == 3);
          if (retentionGuarantorResponse.length) {
            this.retentionGuarantor = retentionGuarantorResponse[0].pivot.amount;
            this.retentionGuarantorNoteCodeDate = retentionGuarantorResponse[0].pivot.note_code_date;
            this.retentionGuarantorNoteCode = retentionGuarantorResponse[0].pivot.note_code;
            this.retentionGuarantorCode = retentionGuarantorResponse[0].pivot.code;
            this.retentionGuarantorDate = retentionGuarantorResponse[0].pivot.date;
          }
        }
        if (response.data.guarantors) {
          this.guarantors=[];
          response.data.guarantors.forEach(g => {
              let guarantor = {
                amount: g.amount,
                identity_card: g.identity_card,
                full_name: g.full_name,
                id: g.affiliate_guarantor_id,
              }
              this.guarantors.push(guarantor);
          });
        }
        TweenLite.to(this.$data, 0.5, { totalRetFun: response.data.total_ret_fun,subTotalRetFun: response.data.sub_total_ret_fun });
        moneyInputMaskAll();
        setTimeout(() => {
          this.$scrollTo('#showEconomicDataTotal');
        }, 800);
      }).catch(error => {
        flash('Error: '+error.response.data.message, 'error');
        this.showEconomicDataTotal = false
      });
    },
    saveTotalRetFun(reload){
      let uri =`/ret_fun/${this.retirementFundId}/save_total_ret_fun`;
      axios.patch(uri,
        {
          advancePayment: parseMoney(this.advancePayment),
          retentionLoanPayment: parseMoney(this.retentionLoanPayment),
          retentionGuarantor: parseMoney(this.retentionGuarantor),

          advancePaymentNoteCode:this.advancePaymentNoteCode,
          advancePaymentNoteCodeDate:this.advancePaymentNoteCodeDate,
          advancePaymentCode:this.advancePaymentCode,
          advancePaymentDate:this.advancePaymentDate,
          retentionLoanPaymentNoteCodeDate:this.retentionLoanPaymentNoteCodeDate,
          retentionLoanPaymentNoteCode:this.retentionLoanPaymentNoteCode,
          retentionLoanPaymentCode:this.retentionLoanPaymentCode,
          retentionLoanPaymentDate:this.retentionLoanPaymentDate,
          retentionGuarantorNoteCodeDate:this.retentionGuarantorNoteCodeDate,
          retentionGuarantorNoteCode:this.retentionGuarantorNoteCode,
          retentionGuarantorCode:this.retentionGuarantorCode,
          retentionGuarantorDate:this.retentionGuarantorDate,
          guarantors: this.guarantors,
          reload,
        }
      ).then(response =>{
        if (reload) {
          flash("Montos recalculados.");
        }else{
          flash("Calculo Total guardado correctamente.");
        }
          this.beneficiaries = response.data.beneficiaries;
          this.totalRetFun = response.data.total_ret_fun;
          this.subTotalRetFun = response.data.sub_total_ret_fun;
          this.showPercentagesRetFun = true;
          setTimeout(() => {
            this.$scrollTo('#showPercentagesRetFun');
          }, 800);
      }).catch(error =>{
          flash("Error al guardar los Datos, Verifique que los beneficiarios tengan parentesco", "error");
          this.showPercentagesRetFun = false;
      });
    },
    // requalificationTotal(index){
    //   this.beneficiaries[index].temp_amount = (this.totalRetFun * this.beneficiaries[index].temp_percentage)/100;
    // },
    savePercentages(reload){
      let uri =`/ret_fun/${this.retirementFundId}/save_percentages`;
      axios.patch(uri,
        {
          beneficiaries: this.beneficiaries,
          reload,
        }
      ).then(response =>{
        if (reload) {
          flash("Montos recalculados.");
        }else{
          flash("Porcentages actualizados a los derechohabientes.");
        }
          this.hasAvailability = response.data.has_availability;
          this.subTotalAvailability = response.data.subtotal_availability;
          this.totalAnnualYield = response.data.total_annual_yield;
          this.totalAvailability = response.data.total_availability;
          this.total = response.data.total;
          this.beneficiariesAvailability = response.data.beneficiaries;
          this.finishRetFun = true,
          this.arrayDiscounts = response.data.array_discounts;
          console.log('Has Availability: '+response.data.has_availability);
          setTimeout(() => {
            if (this.hasAvailability) {
              this.$scrollTo('#hasAvailabilityScroll');
            }else{
              this.$scrollTo('#wrapper');
            }
          }, 800);
      }).catch(error =>{
        console.log(error);
          this.finishRetFun = false,
          flash("Error al guardar los porcentages", "error");
      });
    },
    savePercentagesAvailability(reload){
      let uri =`/ret_fun/${this.retirementFundId}/save_percentages_availability`;
      axios.patch(uri,
        {
          beneficiaries: this.beneficiariesAvailability,
          reload,
        }
      ).then(response =>{
        if(reload){
          flash("Montos recalculados.");
        }else{
          flash("Montos de Disponibilidad Actualizados.");
        }
          this.beneficiariesRetFunAvailability = response.data.beneficiaries;
          this.showPercentagesRetFunAvailability = true;
          setTimeout(() => {
            this.$scrollTo('#showPercentagesRetFunAvailability');
          }, 800);
      }).catch(error =>{
          flash("Error al guardar Montos de Disponibilidad", "error");
          this.showPercentagesRetFunAvailability = false;
      });
    },
    saveTotalRetFunAvailability(){
      let uri =`/ret_fun/${this.retirementFundId}/save_total_ret_fun_availability`;
      axios.patch(uri,
        {
          beneficiaries: this.beneficiariesRetFunAvailability,
        }
      ).then(response =>{
          this.finishAvailability =  true,
          flash("Montos de Fondo de Retiro + Disponibilidad Actualizados.");
          setTimeout(() => {
            this.$scrollTo('#wrapper');
          }, 800);
      }).catch(error =>{
          flash("Error al guardar Montos de Fondo de Retiro + Disponibilidad", "error");
      });
    },
    // requalificationTotalAvailability(index){
    //   this.beneficiariesAvailability[index].temp_amount_availability = (this.totalAvailability * this.beneficiariesAvailability[index].percentage)/100;
    // },
    // requalificationTotalRetFunAvailability(index){
    //   this.beneficiariesRetFunAvailability[index].temp_amount_total = (this.total * this.beneficiariesAvailability[index].percentage)/100;
    // },
    max(a, b){
      return (parseFloat(a.toFixed(2)) > parseFloat(b.toFixed(2)) || isNaN(a));
    },
    colorClass(a, b){
      if (isNaN(a)) {
        return;
      }
      if (parseFloat(a.toFixed(2)) === parseFloat(b.toFixed(2))) {
        return {
          'text-info': true,
        };
      }
      if (parseFloat(a.toFixed(2)) > parseFloat(b.toFixed(2))) {
        return {
          'text-danger': true,
        };
      }
      if (parseFloat(a.toFixed(2)) < parseFloat(b.toFixed(2))) {
        return{
          'text-warning':true,
        }
      }
    }
  },
  computed: {
    totalAverageSalaryQuotableAnimated: function() {
      return this.totalAverageSalaryQuotable;
    },
    totalQuotesAnimated: function() {
      return this.totalQuotes;
    },
    subTotalRetFunAnimated(){
      return this.subTotalRetFun;
    },
    totalAnimated(){
      return this.subTotalRetFun - parseMoney(this.advancePayment) -parseMoney(this.retentionLoanPayment) -parseMoney(this.retentionGuarantor);
    },
    percentageAdvancePayment(){
      return (this.subTotalRetFun > 0 && this.subTotalRetFun != '') ?  (100 * parseMoney(this.advancePayment))/this.subTotalRetFun : 0;
    },
    percentageRetentionLoanPayment(){
      return (this.subTotalRetFun > 0 && this.subTotalRetFun != '') ?  (100 * parseMoney(this.retentionLoanPayment))/this.subTotalRetFun : 0;
    },
    percentageRetentionGuarantor(){
      return (this.subTotalRetFun > 0 && this.subTotalRetFun != '') ?  (100 * parseMoney(this.retentionGuarantor))/this.subTotalRetFun : 0;
    },
    totalPercentageRetFun(){
      const sum = this.beneficiaries.reduce((accumulator, current) => {
        return accumulator + parseFloat(current.temp_percentage);
       }, 0.0);
       return sum;
    },
    totalAmountRetFun(){
      const sum = this.beneficiaries.reduce((accumulator, current) => {
        return accumulator + parseFloat(current.temp_amount);
       }, 0.0);
       return sum;
    },
    totalPercentageAvailability(){
      const sum = this.beneficiariesAvailability.reduce((accumulator, current) => {
        return accumulator + parseFloat(current.percentage);
       }, 0.0);
       return sum;
    },
    totalAmountAvailability(){
      const sum = this.beneficiariesAvailability.reduce((accumulator, current) => {
        return accumulator + parseFloat(current.temp_amount_availability);
       }, 0.0);
       return sum;
    },
    totalPercentageRetFunAvailability(){
      const sum = this.beneficiariesRetFunAvailability.reduce((accumulator, current) => {
        return accumulator + parseFloat(current.percentage);
       }, 0.0);
       return sum;
    },
    totalAmountRetFunAvailability(){
      const sum = this.beneficiariesRetFunAvailability.reduce((accumulator, current) => {
        return accumulator + parseFloat(current.temp_amount_total);
       }, 0.0);
       return sum;
    },
  },
};
</script>

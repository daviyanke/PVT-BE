@extends('layouts.app')

@section('title', 'Afiliados')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
   <div class="col-lg-9">
       {{--  {{ Breadcrumbs::render('show_affiliate', $affiliate) }}  --}}
   </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-md-12">
            <affiliate-police :affiliate="{{ $affiliate }}" inline-template>
                 @include('affiliates.simple_info', ['affiliate'=>$affiliate])
            </affiliate-police>
        </div>
        <div class="col-md-12">
            <div class="ibox-content">
                {!! Form::open(['url' => 'ret_fun', 'method' => 'POST']) !!}
                <input type="hidden" name="affiliate_id" value="{{$affiliate->id}}">
                <form-wizard color="#1AB394" title="" subtitle="" back-button-text="Volver" next-button-text="Siguiente" finish-button-text="Finish"
                    error-color="#ED5565">
                    <tab-content title="Modalidad y Requisitos" icon="mdi mdi-format-list-checks">
                        <ret-fun-step1-requirements :modalities="{{ $modalities }}" :requirements="{{ $requirements }}" inline-template>
                            @include('ret_fun.step1_requirements')
                        </ret-fun-step1-requirements>
                    </tab-content>
                    <tab-content title="Datos del Solicitante" icon="mdi mdi-account-edit">
                        <ret-fun-step2-applicant :cities="{{ $cities }}" :kinships="{{ $kinships }}" :affiliate="{{ $affiliate }}" inline-template>
                            @include('ret_fun.step2_applicant')
                        </ret-fun-step2-applicant>
                    </tab-content>
                    <tab-content title="Datos de los Derechohabientes" icon="mdi mdi-account-multiple-plus">
                        <ret-fun-step3-beneficiaries :items="{{ $ret }}" :kinhsips="{{ $kinships }}" inline-template>
                            @include('ret_fun.step3_beneficiaries')
                        </ret-fun-step3-beneficiaries>
                    </tab-content>
                    {!! Form::submit('Click Me!') !!}
                </form-wizard>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>


@endsection
{{-- Shared form fields used by both create.blade.php and edit.blade.php --}}

<div class="row">
    {{-- Name --}}
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('name', __('Cooperative Name'), ['class' => 'form-label']) }}
            <x-required></x-required>
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Cooperative Name')]) }}
        </div>
    </div>

    {{-- Location (MCC) --}}
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('location', __('Location / MCC'), ['class' => 'form-label']) }}
            {{ Form::text('location', null, ['class' => 'form-control', 'placeholder' => __('Enter MCC or Location Name')]) }}
        </div>
    </div>

    {{-- Leader Name --}}
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('leader_name', __('Leader Name'), ['class' => 'form-label']) }}
            {{ Form::text('leader_name', null, ['class' => 'form-control', 'placeholder' => __('Enter Leader Name')]) }}
        </div>
    </div>

    {{-- Leader Phone --}}
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('leader_phone', __('Leader Phone'), ['class' => 'form-label']) }}
            {{ Form::text('leader_phone', null, [
                'class'       => 'form-control',
                'placeholder' => __('e.g. +234 800 000 0000'),
                'maxlength'   => '20',
                'type'        => 'tel',
            ]) }}
        </div>
    </div>

    {{-- Site Location --}}
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('site_location', __('Site / GPS Location'), ['class' => 'form-label']) }}
            {{ Form::text('site_location', null, ['class' => 'form-control', 'placeholder' => __('Enter Site or GPS Location')]) }}
        </div>
    </div>

    {{-- Formation Date --}}
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('formation_date', __('Formation Date'), ['class' => 'form-label']) }}
            {{ Form::date('formation_date', null, ['class' => 'form-control']) }}
        </div>
    </div>

    {{-- Average Daily Supply --}}
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('average_daily_supply', __('Avg Daily Supply (Litres)'), ['class' => 'form-label']) }}
            {{ Form::number('average_daily_supply', null, [
                'class'       => 'form-control',
                'min'         => '0',
                'step'        => '0.01',
                'placeholder' => __('0.00'),
            ]) }}
        </div>
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <div class="form-group">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', ['active' => __('Active'), 'inactive' => __('Inactive')], 'active', ['class' => 'form-control']) }}
        </div>
    </div>
</div>

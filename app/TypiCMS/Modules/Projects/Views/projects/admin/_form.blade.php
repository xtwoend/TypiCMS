@section('js')
    {{ HTML::script(asset('//tinymce.cachefly.net/4.0/tinymce.min.js')) }}
    {{ HTML::script(asset('js/admin/form.js')) }}
@stop

@include('admin._buttons-form')

{{ Form::hidden('id'); }}

<div class="row">
    <div class="col-sm-4 form-group @if($errors->has('category_id'))has-error @endif">
        {{ Form::label('category_id', trans('validation.attributes.category_id'), array('class' => 'control-label')) }}
        {{ Form::select('category_id', $categories, null, array('class' => 'form-control')) }}
        @if($errors->has('category_id'))
        <span class="help-block">{{ $errors->first('category_id') }}</span>
        @endif
    </div>
</div>

<div class="form-group">
    {{ Form::label('tags', trans('validation.attributes.tags'), array('class' => 'control-label')) }}
    {{ Form::text('tags', $tags, array('id' => 'tags', 'class' => 'form-control')) }}
</div>

@include('admin._tabs-lang')

<div class="tab-content">

    @foreach ($locales as $lang)

    <div class="tab-pane fade @if ($locale == $lang)in active @endif" id="{{ $lang }}">
        <div class="form-group">
            {{ Form::label($lang.'[title]', trans('validation.attributes.title')) }}
            {{ Form::text($lang.'[title]', $model->translate($lang)->title, array('autofocus' => 'autofocus', 'class' => 'form-control')) }}
        </div>
        <div class="form-group @if($errors->has($lang.'.slug'))has-error @endif">
            {{ Form::label($lang.'[slug]', trans('validation.attributes.slug'), array('class' => 'control-label')) }}
            <div class="input-group">
                {{ Form::text($lang.'[slug]', $model->translate($lang)->slug, array('class' => 'form-control')) }}
                <span class="input-group-btn">
                    <button class="btn btn-default btn-slug @if($errors->has($lang.'.slug'))btn-danger @endif" type="button">@lang('validation.attributes.generate')</button>
                </span>
            </div>
            @if($errors->has($lang.'.slug'))
            <span class="help-block">{{ $errors->first($lang.'.slug') }}</span>
            @endif
        </div>
        <div class="form-group">
            <label class="checkbox">
                {{ Form::checkbox($lang.'[status]', 1, $model->translate($lang)->status) }} @lang('validation.attributes.online')
            </label>
        </div>
        <div class="form-group">
            {{ Form::label($lang.'[summary]', trans('validation.attributes.summary')) }}
            {{ Form::textarea($lang.'[summary]', $model->translate($lang)->summary, array('class' => 'form-control', 'rows' => 4)) }}
        </div>
        <div class="form-group">
            {{ Form::label($lang.'[body]', trans('validation.attributes.body')) }}
            {{ Form::textarea($lang.'[body]', $model->translate($lang)->body, array('class' => 'editor form-control')) }}
        </div>
    </div>

    @endforeach

</div>

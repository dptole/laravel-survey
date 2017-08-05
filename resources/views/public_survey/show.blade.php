@extends('main')

@section('title', '/ Survey ' . $survey->name)

@section('content')
  <div class="public-survey-content">
    <h1 class="text-center">Welcome to the survey &quot;{{ $survey->name }}&quot;</h1>

    <table class="table">
      <thead>
        <tr>
          <th>Description</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            {!! nl2br(e($survey->description)) !!}
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <template id="public_survey_template">
    <h1 class="text-center">Question <span class="public-survey-question-number"></span></h1>

    <table class="table table-bordered">
      <thead>
        <tr>
          <th>
            <h2 class="public-survey-question-description"></h2>
          </th>
        </tr>
      </thead>
      <tbody class="public-survey-options"></tbody>
    </table>
  </template>

  <template id="public_survey_answers_template">
    <tr class="check">
      <td class="row">
        <input type="radio" name="check" class="col-xs-1">
        <label class="col-xs-11"></label>
      </td>
    </tr>

    <tr class="free">
      <td class="row">
        <input type="radio" name="check" class="col-xs-1">
        <label class="col-xs-11">
          <input type="text" class="form-control">
        </label>
      </td>
    </tr>
    </div>
  </template>

  <button data-survey-uuid="{{ $survey->uuid }}" data-questions="{{ json_encode($survey->all_questions) }}" class="survey-questions btn btn-success btn-block">Start</button>

  <script type="text/javascript" src="{{ Helper::route('start-survey') }}"></script>
@endsection


@extends('main')

@section('title', '/ Dashboard')

@section('content')
  <h1>Dashboard</h1>

  <table class="table table-bordered{{ count($surveys) > 0 ? ' table-hover' : ''}}">
    <thead>
      <tr>
        <th class="hidden-xs">UUID</th>
        <th>Name</th>
        <th>Status</th>
        <th class="hidden-xs">Last edited</th>
        <th class="hidden-xs"></th>
      </tr>
    </thead>
    <tbody>

    @if(count($surveys) < 1)
      <tr>
        <td colspan="5">
          <h3 class="text-center">
            {{ Html::linkRoute('survey.create', 'Create', [], ['class' => 'btn btn-primary']) }} your first survey.
          </h3>
        </td>
      </tr>
    @endif

    @foreach($surveys as $survey_index => $survey)
      <tr>
        <th class="hidden-xs">{{ $survey->uuid }}</th>
        <td>
          {{ $survey->name }}
          <div class="visible-xs">
            {{ Html::linkRoute('survey.edit', 'Edit', [$survey->uuid], ['class' => 'btn btn-warning btn-xs']) }}
            {{ Html::linkRoute('survey.destroy', 'Delete', [$survey->uuid], ['class' => 'btn btn-danger btn-xs']) }}
          </div>
        </td>
        <td>{{ $survey->status }}</td>
        <td class="hidden-xs">{{ $survey->updated_at }}</td>
        <td class="hidden-xs">
          {{ Html::linkRoute('survey.edit', 'Edit', [$survey->uuid], ['class' => 'btn btn-warning btn-xs']) }}
          {{ Html::linkRoute('survey.destroy', 'Delete', [$survey->uuid], ['class' => 'btn btn-danger btn-xs']) }}
        </td>
      </tr>
    @endforeach

    </tbody>
  </table>

  <div class="text-center">
    {!! $surveys->links() !!}
  </div>
@endsection


import $ from 'jquery'

console.log(
  JSON.stringify(
    $('.survey-questions').data('questions'),
    0,
    2
  )
)

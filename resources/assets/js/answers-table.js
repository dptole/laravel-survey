import $ from 'jquery'
import _ from 'lodash'

// Table css class symbol
const tccs = Symbol()

// Add answer css class symbol
const aaccs = Symbol()

export default class AnswersTable {
  constructor(table_css_class, add_answer_css_class) {
    this[tccs] = $(table_css_class)
    this[aaccs] = $(add_answer_css_class)
    this[aaccs].on('click', _ => this.addAnswer())
  }

  store(key) {
    try {
      sessionStorage[key] = JSON.stringify(
        this[tccs].find('tbody tr').toArray().map((tr, index) => (
          {
            type: $(tr).find('[data-type="type"]').val(),
            value: $(tr).find('[data-type="value"]').val()
          }
        ))
      )
    } catch(e) {
      return false
    }
  }

  addAnswer(value = '', type = 'check') {
    this[tccs].find('tbody').append(
      this.createRowActions(
        this.createAnswer(value, type)
      )
    )
    this.normalizeRows()
  }

  addAnswers(answers) {
    if(!(_.isArray(answers) && answers.every(_.isObject)))
      return false
    answers.forEach(answer => this.addAnswer(answer.value, answer.type))
    return true
  }

  restore(key) {
    try {
      return this.addAnswers(JSON.parse(sessionStorage[key]))
    } catch(e) {
      return false
    }
  }

  countRows() {
    return this[tccs].find('tbody tr').length
  }

  /************************************************/

  removeRow(index) {
    return this.countRows() > 1 && isFinite(index) && index >= 0 && !!this[tccs].find('tbody tr:eq(' + index + ')').remove()
  }

  createRowActions(row) {
    const instance = this

    row.find('td:eq(2) > div > button:eq(0)').on('click', function(event) {
      event.preventDefault()
      const $button = $(this)
          , $row = $button.parents('tr:eq(0)')

      instance.removeRow($row.index())
      instance.normalizeRows()
    })

    row.find('td:eq(2) > div > button:eq(1)').on('click', function(event) {
      event.preventDefault()
      const $button = $(this)
          , $row = $button.parents('tr:eq(0)')

      $row.after(
        instance.createRowActions(
          instance.createFreeAnswer()
        )
      )
      instance.removeRow($row.index())
      instance.normalizeRows()
    })

    return row
  }

  createAnswer(value = '', type = 'check') {
    return type === 'check'
      ? this.createCheckAnswer(value)
      : this.createFreeAnswer()
  }

  createFreeAnswer() {
    return $('<tr>').append(
      $('<td>'),
      $('<td>').append(
        $('<input data-type="value">').attr({
          type: 'hidden',
          name: 'questions_options[][value]',
          value: 'free'
        }),
        $('<input data-type="type">').attr({
          type: 'hidden',
          name: 'questions_options[][type]',
          value: 'free'
        }),
        'Free text'
      ),
      $('<td>').append(
        $('<div>').addClass('pull-right').append(
          $('<button>').addClass('btn btn-danger').text('Remove')
        )
      )
    )
  }

  createCheckAnswer(value = '') {
    return $('<tr>').append(
      $('<td>'),
      $('<td>').append(
        $('<input data-type="value">').attr({
          name: 'questions_options[][value]',
          value
        }).addClass('form-control'),
        $('<input data-type="type">').attr({
          name: 'questions_options[][type]',
          value: 'check',
          type: 'hidden'
        })
      ),
      $('<td>').append(
        $('<div>').addClass('pull-right').append(
          $('<button>').addClass('btn btn-danger').text('Remove'),
          $('<button>').addClass('btn btn-primary').text('Free')
        )
      )
    )
  }

  normalizeRows() {
    this[tccs].find('tbody tr').each((index, tr) => {
      const $tr = $(tr)
          , $value = $tr.find('[data-type="value"]')
          , $type = $tr.find('[data-type="type"]')

      $tr.find('td:eq(0)').text(index)

      if($value.attr('name'))
        $value.attr('name', $value.attr('name').replace(/\[\d*\]/, `[${index}]`))

      if($type.attr('name'))
        $type.attr('name', $type.attr('name').replace(/\[\d*\]/, `[${index}]`))
    })
  }
}


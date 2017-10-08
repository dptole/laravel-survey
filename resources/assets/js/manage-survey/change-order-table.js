import $ from 'jquery'

const log = console.log

export default class ChangeOrderTable {
  constructor({change_order_css_class, move_up_css_class, move_down_css_class}) {
    this.changing_order = false
    this.dom_selected_row = null
    this.dom_move_up_down = $('<div>').text('Move row up / down').addClass('pull-right')
    this.dom_changing_order = $(change_order_css_class)
    this.dom_move_up = $(move_up_css_class)
    this.dom_move_down = $(move_down_css_class)

    this.changeOrderStart()
    this.dom_move_up.on('click', event => {
      event.preventDefault()
      this.moveRow('up', this.dom_selected_row)
    })
    this.dom_move_down.on('click', event => {
      event.preventDefault()
      this.moveRow('down', this.dom_selected_row)
    })
  }

  changeOrderStart() {
    this.changing_order = true
    this.upDownToggleButtons('hide')

    const rows = this.dom_changing_order.find('tr:gt(0)')
    rows.css({
      cursor: 'pointer'
    }).each((index, tr) => {
      const row = $(tr)
      row.on('click', event => {
        event.preventDefault()
        this.selectRow(row)
      })
    })

    this.selectRow(rows.eq(0))
  }

  selectRow(row) {
    if(this.dom_selected_row === row)
      this.removeSelectedRow()
    else {
      this.dom_selected_row = row
      this.upDownToggleButtons('show')
      row.find('td:last-child').append(this.dom_move_up_down)
    }
  }

  upDownToggleButtons(show_function) {
    this.dom_move_up[show_function]()
    this.dom_move_down[show_function]()
  }

  removeSelectedRow() {
    delete this.dom_selected_row
    this.upDownToggleButtons('hide')
    this.dom_move_up_down.remove()
  }

  moveRow(direction, row) {
    if(direction === 'up' && row.prev())
      row.prev().before(row)
    else if(direction === 'down' && row.next())
      row.next().after(row)
    row.parent().children().each((index, row) =>
      $(row).find('td:nth-child(1)').text(index + 1)
    )
  }
}

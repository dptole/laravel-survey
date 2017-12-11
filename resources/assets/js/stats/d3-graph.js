import d3 from 'd3'
import $ from 'jquery'
import lodash from 'lodash'

const d3Graph = {
  svg: null,
  margins: {
    top: 40,
    right: 20,
    bottom: 40,
    left: 50
  },

  init: _ => {
    'drawBars drawLines'.split(' ').forEach(fn => {
      const old_func = d3Graph[fn]
      d3Graph[fn] = (...args) => {
        d3Graph.reload = lodash.debounce(_ => {
          $('svg > g').remove()
          old_func.apply(d3Graph, args)
        }, 100)

        d3Graph.getSVG()
        const g = d3Graph.svg.select('g')

        if(g.node())
          d3Graph.fadeOut(g, function() {
            $('svg > g').remove()
            old_func.apply(d3Graph, args)
          })
        else
          old_func.apply(d3Graph, args)
      }
    })
  },
  getOuterWidth: _ => {
    return $(window).width() > 500 ? 500 : $(window).width() * 0.8
  },
  getOuterHeight: _ => {
    return 250
  },
  getSVG: _ => {
    if(!d3Graph.svg) {
      $('span.svg-loader').remove()
      d3Graph.svg = d3.select('.svg-container').append('svg')
    }
  },
  drawLines: data => {
    /*
      data = [{
        "date": "2017-12-10",
        "answers": 2
      }, {
        "date": "2017-12-08",
        "answers": 1
      }, {
        "date": "2017-12-07",
        "answers": 3
      }]
    */
  },
  drawBars(data, {x_column, y_column, x_axis_title, y_axis_title, graph_title, func_go_back, table_version, on_click_bar}) {
    const outer_width = d3Graph.getOuterWidth()
        , outer_height = d3Graph.getOuterHeight()
        , inner_width = outer_width - d3Graph.margins.left - d3Graph.margins.right
        , inner_height = outer_height - d3Graph.margins.top - d3Graph.margins.bottom
        , x_scale_spaces = 0.3
        , x_scale = d3.scale.ordinal().rangeBands([0, inner_width], x_scale_spaces)
        , y_scale = d3.scale.linear().range([inner_height, 0])
        , colors = d3.scale.category10()
        , g = d3Graph.svg.append('g').attr('transform', 'translate(' + d3Graph.margins.left + ', ' + d3Graph.margins.top + ')')
        , x_axis_g = g.append('g').attr('class', 'd3-axis').attr('transform', 'translate(0, ' + inner_height + ')')
        , x_axis = d3.svg.axis().scale(x_scale).orient('bottom').outerTickSize(0)
        , y_axis_g = g.append('g').attr('class', 'd3-axis').attr('transform', 'translate(0, 0)')
        , y_axis = d3.svg.axis().scale(y_scale).orient('left').ticks(5).outerTickSize(0).tickFormat(d3.format('d'))
        , y_axis_text = y_axis_g.append('text').style('text-anchor', 'middle').text(y_axis_title).attr('transform', function() {
            return 'translate(' + (-d3Graph.margins.left + this.getBBox().height) + ', ' + (inner_height / 2) + ') rotate(-90)'
          })
        , x_axis_text = x_axis_g.append('text').style('text-anchor', 'middle').text(x_axis_title).attr('transform', 'translate(' + (inner_width / 2) + ', ' + (d3Graph.margins.bottom - 5) + ')')
        , graph_text = g.append('text').style('text-anchor', 'middle').text(graph_title).attr('transform', function() {
            return 'translate(' + (outer_width / 2 - d3Graph.margins.left) + ', ' + (this.getBBox().height - d3Graph.margins.bottom) + ')'
          })
        , go_back_title = '&larr; Back'
        , go_back_text = g.append('text').style('text-anchor', 'left').html(go_back_title).attr('class', 'svg-clickable').attr('transform', function() {
            return 'translate(' + (-d3Graph.margins.left) + ', ' + (-d3Graph.margins.top + this.getBBox().height) + ')'
          })

    d3Graph.showTableVersion(table_version)

    x_scale.domain(data.map(d => d[x_column]))
    y_scale.domain([0, d3.max(data, d => d[y_column])])

    d3Graph.resizeSVG(outer_width, outer_height)

    const bars = g
          .selectAll('rect')
          .data(data)

    x_axis_g.call(x_axis)
    y_axis_g.call(y_axis)

    x_axis_g.call(d3Graph.fadeIn)
    y_axis_g.call(d3Graph.fadeIn)
    x_axis_text.call(d3Graph.fadeIn)
    y_axis_text.call(d3Graph.fadeIn)
    graph_text.call(d3Graph.fadeIn)
    if(typeof func_go_back === 'function') {
      go_back_text.call(d3Graph.fadeIn)
      go_back_text.on('click', func_go_back)
    } else
      go_back_text.style('display', 'none')

    // Enter
    bars
      .enter()
      .append('rect')
      .attr('class', 'svg-clickable')
      .attr('x', d => x_scale(d[x_column]))
      .attr('y', inner_height)
      .attr('height', 0)
      .attr('fill', d => colors(d[x_column] + d[y_column]))
      .attr('width', x_scale.rangeBand())
      .on('mouseover', d3Graph.wrapperMouseOverRect(g, {x_scale, x_column, y_scale, y_column}))
      .on('mouseleave', d3Graph.wrapperRemoveTextOverRect(g))
      .on('click', on_click_bar)

    // Update
    d3Graph.barsRise(bars, {inner_height, y_scale, y_column})

    // Exit
    bars
      .exit()
      .remove()
  },
  barsRise(bars, {inner_height, y_scale, y_column}) {
    bars
      .transition()
      .delay((d, i) => i * 100)
      .duration(1000)
      .attr('y', d => y_scale(d[y_column]))
      .attr('height', d => inner_height - y_scale(d[y_column]))
  },
  wrapperMouseOverRect(g, {x_scale, x_column, y_scale, y_column}) {
    return d => {
      const svg_text_over_rect_offset = 5

      g
        .append('text')
        .attr('class', 'svg-text-over-rect')
        .text(d[y_column])
        .attr('x', function() {
          const bbox = this.getBBox()
          return x_scale(d[x_column]) + (x_scale.rangeBand() >> 1) - (bbox.width >> 1)
        })
        .attr('y', y_scale(d[y_column]) - svg_text_over_rect_offset)
        .style('opacity', 0)
        .transition()
        .duration(400)
        .style('opacity', 1)
    }
  },
  fadeOutGraph({g, x_axis_g, y_axis_g, x_axis_text, y_axis_text, graph_text, go_back_text, bars}) {
    return new Promise(resolve => {
      d3Graph.wrapperRemoveTextOverRect(g)()
      x_axis_g.call(d3Graph.fadeOut)
      y_axis_g.call(d3Graph.fadeOut)
      x_axis_text.call(d3Graph.fadeOut)
      y_axis_text.call(d3Graph.fadeOut)
      graph_text.call(d3Graph.fadeOut)
      go_back_text && go_back_text.call(d3Graph.fadeOut)
      bars.call(d3Graph.fadeOut)
      setTimeout(resolve, 900)
    })
  },
  fadeIn(selection) {
    return selection
      .style('display', 'block')
      .style('opacity', 0)
      .transition()
      .duration(800)
      .style('opacity', 1)
  },
  fadeOut(selection, callback) {
    return selection
      .style('opacity', 1)
      .style('display', 'block')
      .transition()
      .duration(800)
      .style('opacity', 0)
      .each('end', function() {
        d3.select(this).style('display', 'none')
        if(typeof callback === 'function')
          callback()
      })
  },
  wrapperRemoveTextOverRect(g) {
    return _ =>
      g.selectAll('text.svg-text-over-rect')
        .transition()
        .duration(400)
        .style('opacity', 0)
        .each('end', function() {
          d3.select(this).remove()
        })
  },
  showTableVersion(version) {
    $('.table-versions').addClass('hide')
    $('.table-version-' + version).removeClass('hide')
  },
  resizeSVG(outer_width, outer_height) {
    if(!d3Graph.svg) return false
    d3Graph.svg
      .attr('width', outer_width)
      .attr('height', outer_height)
    return true
  }
}

d3Graph.init()

export default d3Graph

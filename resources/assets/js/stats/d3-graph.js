import d3 from 'd3'
import $ from 'jquery'
import lodash from 'lodash'

function getData() {
  return $survey_d3_data_json
}

const d3Graph = {
  svg: null,
  drawBars: _ => {
    if(!d3Graph.svg) {
      $('span.svg-loader').remove()
      d3Graph.svg = d3.select('.svg-container').append('svg')
    }

    d3Graph.showTableVersion()
    const svg = d3Graph.svg
    svg.selectAll('*').remove()

    d3Graph.reload = lodash.debounce(d3Graph.drawBars, 100)

    const data = getData()
        , margins = {
            top: 40,
            right: 20,
            bottom: 40,
            left: 50
          }
        , outer_width = $(window).width() > 500 ? 500 : $(window).width() * 0.8
        , outer_height = 250
        , x_column = 'version'
        , y_column = 'total'
        , inner_width = outer_width - margins.left - margins.right
        , inner_height = outer_height - margins.top - margins.bottom
        , x_scale_spaces = 0.3
        , x_scale = d3.scale.ordinal().rangeBands([0, inner_width], x_scale_spaces)
        , y_scale = d3.scale.linear().range([inner_height, 0])
        , colors = d3.scale.category10()
        , g = svg.append('g').attr('transform', 'translate(' + margins.left + ', ' + margins.top + ')')
        , x_axis_g = g.append('g').attr('class', 'd3-axis').attr('transform', 'translate(0, ' + inner_height + ')')
        , x_axis = d3.svg.axis().scale(x_scale).orient('bottom').outerTickSize(0)
        , y_axis_g = g.append('g').attr('class', 'd3-axis').attr('transform', 'translate(0, 0)')
        , y_axis = d3.svg.axis().scale(y_scale).orient('left').ticks(5).outerTickSize(0).tickFormat(d3.format('d'))
        , y_axis_text_height = 10
        , y_axis_title = 'Answers'
        , y_axis_text = y_axis_g.append('text').style('text-anchor', 'middle').attr('transform', 'translate(' + (-margins.left + y_axis_text_height) + ', ' + (inner_height / 2) + ') rotate(-90)').text(y_axis_title)
        , x_axis_title = 'Versions'
        , x_axis_text = x_axis_g.append('text').style('text-anchor', 'middle').attr('transform', 'translate(' + (inner_width / 2) + ', ' + margins.bottom + ')').text(x_axis_title)
        , graph_title_height = 10
        , graph_title = 'Answers by survey version'
        , graph_text = svg.append('text').style('text-anchor', 'middle').attr('transform', 'translate(' + (outer_width / 2) + ', ' + graph_title_height + ')').text(graph_title)
        , go_back_title_height = 10
        , go_back_title = '&larr; Back'
        , go_back_text = svg.append('text').style({'text-anchor': 'left', display: 'none'}).attr('class', 'svg-clickable').attr('transform', 'translate(0, ' + go_back_title_height + ')').html(go_back_title)

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
      .on('click', d => {
        d3Graph.reload = lodash.debounce(_ => {
          d3Graph.showTableVersion(d.version)
          g.selectAll('*').remove()

          const outer_width = $(window).width() > 500 ? 500 : $(window).width() * 0.8
              , inner_width = outer_width - margins.left - margins.right

          d3Graph.resizeSVG(outer_width, outer_height)

          const x_scale = d3.scale.ordinal().rangeBands([0, inner_width], x_scale_spaces)
              , y_scale = d3.scale.linear().range([inner_height, 0])
              , x_column = 'type'
              , y_column = 'total'
              , x_axis_g = g.append('g').attr('class', 'd3-axis').attr('transform', 'translate(0, ' + inner_height + ')')
              , x_axis = d3.svg.axis().scale(x_scale).orient('bottom').outerTickSize(0)
              , y_axis_g = g.append('g').attr('class', 'd3-axis').attr('transform', 'translate(0, 0)')
              , y_axis = d3.svg.axis().scale(y_scale).orient('left').outerTickSize(0).ticks(5).tickFormat(d3.format('d'))
              , y_axis_text_height = 10
              , y_axis_title = 'Answered'
              , y_axis_text = y_axis_g.append('text').style('text-anchor', 'middle').attr('transform', 'translate(' + (-margins.left + y_axis_text_height) + ', ' + (inner_height / 2) + ') rotate(-90)').text(y_axis_title)
              , x_axis_title = 'Completeness'
              , x_axis_text = x_axis_g.append('text').style('text-anchor', 'middle').attr('transform', 'translate(' + (inner_width / 2) + ', ' + (margins.bottom * 0.9) + ')').text(x_axis_title)
              , data = [{
                  type: 'fully',
                  total: d.fully_answered
                }, {
                  type: 'partially',
                  total: d.not_fully_answered
                }]

          x_scale.domain(data.map(d => d[x_column]))
          y_scale.domain([0, d3.max(data, d => d[y_column])])

          x_axis_g.call(x_axis)
          y_axis_g.call(y_axis)

          g
            .selectAll('rect')
            .remove()

          const bars = g
            .selectAll('rect')
            .data(data)
            .enter()
            .append('rect')
            .attr('x', d => x_scale(d.type))
            .attr('y', inner_height)
            .attr('height', 0)
            .attr('fill', d => colors(d[x_column] + d[y_column]))
            .attr('width', x_scale.rangeBand())
            .on('mouseover', d3Graph.wrapperMouseOverRect(g, {x_scale, x_column, y_scale, y_column}))
            .on('mouseleave', d3Graph.wrapperRemoveTextOverRect(g))

          d3Graph.barsRise(bars, {inner_height, y_scale, y_column})

          go_back_text.call(d3Graph.fadeIn)
          x_axis_g.call(d3Graph.fadeIn)
          y_axis_g.call(d3Graph.fadeIn)
          x_axis_text.call(d3Graph.fadeIn)
          y_axis_text.call(d3Graph.fadeIn)

          go_back_text
            .on('click', _ => {
              d3Graph.showTableVersion()
              d3Graph.fadeOutGraph({
                g,
                x_axis_g,
                y_axis_g,
                x_axis_text,
                y_axis_text,
                graph_text,
                go_back_text,
                bars
              }).then(_ =>
                d3Graph.drawBars()
              )
            })

          graph_text
            .text('Survey version ' + d.version)
            .call(d3Graph.fadeIn)
        }, 100)

        d3Graph.fadeOutGraph({
          g,
          x_axis_g,
          y_axis_g,
          x_axis_text,
          y_axis_text,
          graph_text,
          bars
        }).then(d3Graph.reload)
      })
      .on('mouseover', d3Graph.wrapperMouseOverRect(g, {x_scale, x_column, y_scale, y_column}))
      .on('mouseleave', d3Graph.wrapperRemoveTextOverRect(g))

    // Update
    d3Graph.barsRise(bars, {inner_height, y_scale, y_column})

    // Exit
    bars
      .exit()
      .remove()

    d3Graph.showTableVersion()
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
        .attr('x', x_scale(d[x_column]) + x_scale.rangeBand() / 2.3)
        .attr('y', y_scale(d[y_column]) - svg_text_over_rect_offset)
        .text(d[y_column])
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
  fadeOut(selection) {
    return selection
      .style('opacity', 1)
      .style('display', 'block')
      .transition()
      .duration(800)
      .style('opacity', 0)
      .each('end', function() {
        d3.select(this).style('display', 'none')
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

export default d3Graph

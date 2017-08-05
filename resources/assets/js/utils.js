import _ from 'lodash'

const utils = {
  goToProperty(object, properties) {
    properties = _.isString(properties) ? properties.split('.') : []
    while(object && properties.length)
      object = object[properties.shift()]
    return object
  }
}

export default utils

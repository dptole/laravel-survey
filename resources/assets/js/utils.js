import _ from 'lodash'

const utils = {
  goToProperty(object, properties) {
    properties = _.isString(properties) ? properties.split('.') : []
    while(object && properties.length)
      object = object[properties.shift()]
    return object
  },

  getProperties(object) {
    function validateProperty(object, property) {
      return _.isFunction(object[property]) || object[property] === undefined || object[property] === null ? undefined : object[property]
    }

    const properties = {}
        , for_loop_properties = []

    if(_.isObject(object)) {
      for(const property in object) for_loop_properties.push(property)

      Object.keys(object)
        .concat(Object.getOwnPropertyNames(object))
        .concat(for_loop_properties)
        .reduce(
          (acc, key) => (acc[key] = validateProperty(object, key), acc),
          properties
        )
    }

    return properties
  },

  async getBattery() {
    try {
      return await navigator.getBattery()
    } catch(error) {
      return error
    }
  }
}

export default utils

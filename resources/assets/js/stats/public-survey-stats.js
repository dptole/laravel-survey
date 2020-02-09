import Echo from 'laravel-echo'

const Pusher = require('pusher-js')

export default class PublicSurveyStats {
  constructor() {
    const instance = this

    this.websocket = {
      socket: null,
      channel: null,

      channel(channel) {
        instance.websocket.channel = instance.websocket.socket.channel(channel)
        instance.websocket.channel.subscribe()
        return instance.websocket
      },

      on(event_name, callback) {
        instance.websocket.channel.on(event_name, callback)
        return instance.websocket
      },

      config(options) {
        instance.websocket.socket = new Echo(options)
        return instance.websocket
      }
    }
  }
}


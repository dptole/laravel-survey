import $ from 'jquery'

// VARIABLES

// 0 = unset
// 1 = error    = invalid site key)
// 2 = expired  = valid site key & token pending
// 3 = success  = valid site key & token generated
const RECAPTCHA_STATUS_UNSET = 0
const RECAPTCHA_STATUS_ERROR = 1
const RECAPTCHA_STATUS_EXPIRED = 2
const RECAPTCHA_STATUS_SUCCESS = 3

let site_key_status = RECAPTCHA_STATUS_UNSET
let recaptcha_token = null



// FUNCTIONS

const cantSave = () => $('.setup-save').attr('disabled', true)
const canSave = () => $('.setup-save').attr('disabled', false)

const isReCaptchaStatusSuccess = () => {
  const rm = getReCaptchaModel()

  return typeof rm.recaptcha_token === 'string' &&
    rm.recaptcha_token.length > 0 &&
    rm.site_key_status === RECAPTCHA_STATUS_SUCCESS
}

const getReCaptchaModel = () =>
  ({
    recaptcha_token,
    site_key_status
  })

const toggleReCaptchaKeysAvailability = readonly => {
  $('#GOOGLE_RECAPTCHA_SITE_SECRET').attr('readonly', readonly)
  $('#GOOGLE_RECAPTCHA_SITE_KEY').attr('readonly', readonly)
}

const togglePusherAvailability = readonly => {
  $('#PUSHER_APP_ID').attr('readonly', readonly)
  $('#PUSHER_APP_KEY').attr('readonly', readonly)
  $('#PUSHER_APP_SECRET').attr('readonly', readonly)
  $('#PUSHER_APP_CLUSTER').attr('readonly', readonly)
}

const recycleReCaptcha = () => {
  site_key_status = RECAPTCHA_STATUS_UNSET
  recaptcha_token = null

  const old_dynamic_g_recaptcha = $('.GOOGLE_RECAPTCHA_ELEMENT')
  const dynamic_g_recaptcha = old_dynamic_g_recaptcha.get(0).cloneNode()

  old_dynamic_g_recaptcha.before(dynamic_g_recaptcha)
  old_dynamic_g_recaptcha.remove()

  return dynamic_g_recaptcha
}

const injectReCaptchaToken = () => {
  const rm = getReCaptchaModel()
  const form = $('form[name=setup-update-missing-configs]')
  const token = form.find(' > input[type=hidden]#GOOGLE_RECAPTCHA_TOKEN')

  if(token.length === 0)
    $('<input>').attr({
      id: 'GOOGLE_RECAPTCHA_TOKEN',
      name: 'GOOGLE_RECAPTCHA_TOKEN',
      type: 'hidden'
    }).val(rm.recaptcha_token).prependTo(form)
  else
    token.val(rm.recaptcha_token)
}

const tickReCaptchaStatus = () => {
  if(site_key_status === RECAPTCHA_STATUS_UNSET || site_key_status === RECAPTCHA_STATUS_ERROR) {
    toggleReCaptchaKeysAvailability(false)
    canSave()

  } else if(site_key_status === RECAPTCHA_STATUS_EXPIRED) {
    cantSave()

  } else if(isReCaptchaStatusSuccess()) {
    toggleReCaptchaKeysAvailability(true)
    canSave()

  } else {
    toggleReCaptchaKeysAvailability(true)
    cantSave()
  }
}



// EVENT LISTENERS

$('.setup-save').on('click', e => {
  if(!$('#GOOGLE_RECAPTCHA_ENABLED').is(':checked') || isReCaptchaStatusSuccess()) {
    injectReCaptchaToken()
    return;
  }

  e.preventDefault()

  const dynamic_g_recaptcha = recycleReCaptcha()

  grecaptcha.ready(() => {
    try {
      grecaptcha.render(
        dynamic_g_recaptcha,
        {
          sitekey: $('#GOOGLE_RECAPTCHA_SITE_KEY').val(),

          callback: response => {
            recaptcha_token = response
            site_key_status = RECAPTCHA_STATUS_SUCCESS
            tickReCaptchaStatus()
          },

          'error-callback': () => {
            recaptcha_token = null
            site_key_status = RECAPTCHA_STATUS_ERROR
            tickReCaptchaStatus()
          },

          'expired-callback': () =>  {
            recaptcha_token = null
            site_key_status = RECAPTCHA_STATUS_EXPIRED
            tickReCaptchaStatus()
          }
        }
      )

      const ifr = dynamic_g_recaptcha.querySelector('iframe')
      if(ifr)
        ifr.onload = () => {
          site_key_status = site_key_status === RECAPTCHA_STATUS_UNSET ? RECAPTCHA_STATUS_SUCCESS : site_key_status
          tickReCaptchaStatus()
        }
    } catch(error) {
      $('.GOOGLE_RECAPTCHA_ELEMENT').text(error.name + ': ' + error.message)
      recaptcha_token = null
      site_key_status = RECAPTCHA_STATUS_UNSET
      tickReCaptchaStatus()
    }
  })
})

$('#PUSHER_ENABLED').on('change', e => {
  togglePusherAvailability(!e.target.checked)
})

$('#GOOGLE_RECAPTCHA_ENABLED').on('change', e => {
  toggleReCaptchaKeysAvailability(!e.target.checked)

  if(e.target.checked) {
    tickReCaptchaStatus()
  } else {
    canSave()
    recycleReCaptcha()
  }
})

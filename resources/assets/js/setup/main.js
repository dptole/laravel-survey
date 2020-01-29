import $ from 'jquery'

// VARIABLES

// 0 = unset
// 1 = error
// 2 = success
let site_key_status = 0
let recaptcha_token = null



// FUNCTIONS

const cantSave = () => $('.setup-save').attr('disabled', true)
const canSave = () => $('.setup-save').attr('disabled', false)

const isReCaptchaStatusReady = () => {
  const rs = getReCaptchaStatus()

  return typeof rs.recaptcha_token === 'string' &&
    rs.recaptcha_token.length > 0 &&
    rs.site_key_status === 2
}

const getReCaptchaStatus = () =>
  ({
    recaptcha_token,
    site_key_status
  })

const tickReCaptchaStatus = () => {
  if(site_key_status < 2)
    canSave()

  else if(!isReCaptchaStatusReady()) {
    toggleReCaptchaKeysAvailability(true)
    cantSave()
  }
}

const recycleReCaptcha = () => {
  site_key_status = 0
  recaptcha_token = null

  const old_dynamic_g_recaptcha = $('.GOOGLE_RECAPTCHA_ELEMENT')
  const dynamic_g_recaptcha = old_dynamic_g_recaptcha.get(0).cloneNode()

  old_dynamic_g_recaptcha.before(dynamic_g_recaptcha)
  old_dynamic_g_recaptcha.remove()

  return dynamic_g_recaptcha
}

const toggleReCaptchaKeysAvailability = disabled => {
  $('#GOOGLE_RECAPTCHA_SITE_SECRET').attr('disabled', disabled)
  $('#GOOGLE_RECAPTCHA_SITE_KEY').attr('disabled', disabled)
}

const togglePusherAvailability = disabled => {
  $('#PUSHER_APP_ID').attr('disabled', disabled)
  $('#PUSHER_APP_KEY').attr('disabled', disabled)
  $('#PUSHER_APP_SECRET').attr('disabled', disabled)
  $('#PUSHER_APP_CLUSTER').attr('disabled', disabled)
}



// EVENT LISTENERS

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

$('.setup-save').on('click', e => {
  if(!$('#GOOGLE_RECAPTCHA_ENABLED').is(':checked'))
    return;

  e.preventDefault()

  const dynamic_g_recaptcha = recycleReCaptcha()

  grecaptcha.ready(() => {
    grecaptcha.render(
      dynamic_g_recaptcha,
      {
        sitekey: $('#GOOGLE_RECAPTCHA_SITE_KEY').val(),

        callback: response => {
          recaptcha_token = response
          tickReCaptchaStatus()
        },

        'error-callback': () => {
          site_key_status = 1
          tickReCaptchaStatus()
        },

        'expired-callback': () =>  {
          recaptcha_token = null
          tickReCaptchaStatus()
        }
      }
    )

    const ifr = dynamic_g_recaptcha.querySelector('iframe')
    if(ifr)
      ifr.onload = () => {
        site_key_status = site_key_status === 0 ? 2 : site_key_status
        tickReCaptchaStatus()
      }
  })
})


$ ->

  login = $('#loginform')
  recover = $('#recoverform')
  authbox = $('#authbox')
  speed = 400;
  $('#to-recover').click ()->
    $('a.close', authbox).click()
    login.fadeTo(speed, 0.01).hide()
    recover.fadeTo(speed, 1).show()

  $('#to-login').click ()->
    $('a.close', authbox).click()
    recover.fadeTo(speed, 0.01).hide()
    login.fadeTo(speed, 1).show()

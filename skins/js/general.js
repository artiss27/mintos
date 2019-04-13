jQuery(document).ready(function ($) {
  $('form').append('<input type="hidden" name="xsrf" value="' + $('body').attr('data-xsrf') + '">'); // add xsrf protection
  auth.init(); //enable authorization
  formPreloader.init(); // enable form preloader
  //formPreloader.setPreloader('#authSave');
});

/**
 * lock button submit in the form of repeated or unnecessary pressing
 * @type {{init: formPreloader.init, setPreloader: formPreloader.setPreloader, removePreloader: formPreloader.removePreloader}}
 */
let formPreloader = {

  init: function () {
    const self = this;
    $('form:not([data-ajax])').submit(function (e) {
      $('form input[type="submit"], .submit').each(function () {
        self.setPreloader(this);
      });
    });
  },

  setPreloader: function (btn) {
    let width = $(btn).outerWidth() + 'px';
    let height = $(btn).outerHeight() + 'px';
    $(btn).attr('hidden', true).after('<div class="' + $(btn).attr("class") + ' stub" style="width: ' + width + '; height: ' + height + '"><i class="fas fa-sync fa-spin"></i></div>');
  },

  removePreloader: function (btn) {
    $(btn).attr('hidden', false).siblings('.stub').remove();
  }
};

/**
 * get an error on value type
 * @param value
 * @param type
 * @returns {string}
 */
function errorValidation(value, type) {
  let error = '';
  if (value === '') {
    error = 'The field must not be empty';
  } else if (type === 'email') {
    let pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
    if (!pattern.test(value)) {
      error = 'Correct e-mail is required for registration';
    }
  } else if (type === 'password') {
    if (value.length < 7) {
      error = 'Password must be at least 7 characters';
    }
  }
  return error;
}

/**
 * authorization, registration form
 * @type {{getAuthForm: auth.getAuthForm, getForgotPass: auth.getForgotPass, init: auth.init, checkPassword: (function(): boolean), action: string, resetForm: auth.resetForm, getAction: auth.getAction, checkEmail: auth.checkEmail, getRegisterForm: auth.getRegisterForm, setFeedback: auth.setFeedback}}
 */
let auth = {
  action: 'login',

  init: function () {
    const self = this;
    self.getAction();

    $('.authAction').click(function () {
      self.action = $(this).attr('data-action');
      if (!self.action) return;
      self.resetForm();
      self.getAction();
    });

    $('#authSave').click(function () {
      let error;
      if (self.action === 'register') {
        if (self.verifyAllFields()) self.checkEmail(true);
      } else if (error = errorValidation($('#email').val(), 'email')) {
        self.setFeedback($('#email'), error);
      } else {
        self.setResult();
      }
    });

    $('#authModal').on('blur', 'input', function (event) {
      if (event.relatedTarget && event.relatedTarget.id === 'authSave') return;

      const field = $(this).attr('id');
      $(this).siblings('.error').html('').attr('class', 'error');

      if (field === 'email') {
        if (self.action === 'register') {
          self.checkEmail();
        }

      } else if (field === 'password') {
        if (self.action === 'register') {
          let error = errorValidation($(this).val(), 'password');
          self.setFeedback($(this), error);
        }

      } else {
        let error = errorValidation($(this).val(), 'string');
        self.setFeedback($(this), error);
      }
    });

    $('#authModal').on('focus', 'input', function () {
      $(this).attr('class', 'form-control').siblings('.error').html('').attr('class', 'error');
    });
  },

  getAction: function () {
    const self = this;
    $('#authModalBody [data-role]').each(function () {
      if ($(this).attr('data-role').indexOf(self.action) + 1) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
    switch (this.action) {
      case 'register':
        this.getRegisterForm();
        break;
      case 'login':
        this.getAuthForm();
        break;
      case 'restore':
        this.getForgotPass();
        break;
    }
  },

  getRegisterForm: function () {
    $('#authModalLabel').text('Register');
    $('#authSave').text('Save');
    $('#authText').text('Already a member? ');
    $('#authAction').text('Login').attr('data-action', 'login');
  },

  getAuthForm: function () {
    $('#authModalLabel').text('Log in');
    $('#authSave').text('Login');
    $('#authText').text('No account yet? ');
    $('#authAction').text('Register').attr('data-action', 'register');
  },

  getForgotPass: function () {
    $('#authModalLabel').text('Password recovery');
    $('#authSave').text('Restore');
    $('#authText').text('');
    $('#authAction').text('Back to Login').attr('data-action', 'login');
  },

  checkEmail: function (save) {
    const self = this;
    let email = $('#email').val();
    let error = errorValidation(email, 'email');
    self.setFeedback($('#email'), error);
    if (error) return false;
    $.ajax({
      url: "/api/user/chack-email",
      type: 'post',
      data: {'email': email},
      dataType: 'json',
      success: function (response) {
        let text = (response.error && response.error.email ? response.error.email : '');
        self.setFeedback($('#email'), text);
        if (!save || response.error) {
          return (response.error ? false : true);
        } else {
          self.setResult();
        }
      }
    });
  },

  verifyAllFields: function () {
    const self = this;
    let error = false;
    let text = '';
    $('input:not(:hidden)').each(function () {
      if (text = errorValidation($(this).val(), $(this).attr('id'))) {
        self.setFeedback($(this), text);
        error = true;
      }
    });
    return !error;
  },

  setFeedback: function ($elem, error) {
    let prefix = (error ? 'in' : '');
    error = (error ? error : '');
    $elem.addClass(`is-${prefix}valid`).siblings('.error').addClass(`${prefix}valid-feedback`).html(error);
  },

  resetForm: function () {
    $('#authModal .error').html('').attr('class', 'error');
    $('#authModal input:not([name="xsrf"])').val('').attr('class', 'form-control');
  },

  setResult: function () {
    const self = this;
    formPreloader.setPreloader('#authSave');
    let data = $('#authForm').serialize() + '&action=' + self.action;
    $.ajax({
      url: "/api/user/login",
      type: 'post',
      data: data,
      dataType: 'json',
      success: function (response) {
        // console.log(response);

        if (response.status === 'ok') {
          if (self.action === 'restore') {
            alert('A password recovery letter has been sent to your Email. Check your mail!');
          } else {
            window.location.reload();
          }

        } else if (response.error) {
          let genError = '';
          for (let key in response.error) {
            let $elem = $('#' + key);
            if ($elem.length) {
              self.setFeedback($elem, response.error[key]);
            } else {
              genError = response.error[key];
            }
          }
          if (genError) {
            $('#authForm').siblings('.error').html('<div class="alert alert-danger text-center" role="alert">' + genError + '</div>');
          } else {
            $('#authForm').siblings('.error').html('');
          }
        }
        formPreloader.removePreloader('#authSave');
      }
    });
  }
};
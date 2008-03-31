Sabel.PHP.FormValidator = function(columns) {
  this.init();

  var v = this.validator;
  var errors = columns.errors;

  var elms = this.form.elements;
  for (var i = 0, el; el = elms[i]; i++) {
    if (el.tagName == "INPUT") {
      if (el.name.indexOf("::") > -1) {
        var n = el.name.substr(el.name.indexOf("::")+2);

        this.addValidator(el, columns.data[n], errors);
      }
    }
  }
}

Sabel.PHP.FormValidator.prototype = {
  form: null,
  validator: null,

  init: function() {
    var scripts = document.getElementsByTagName("script");
    var elm = scripts[scripts.length - 1];
    while (elm = elm.previousSibling) {
      if (elm.nodeType === 1 && elm.tagName === "FORM") {
        this.form = elm;
        this.validator = new Sabel.Validator(elm);
        break;
      }
    }
  },

  addValidator: function(el, column, errors) {
    var type = column.TYPE, v = this.validator;

    if (column.NULLABLE == false) v.add(el, Sabel.Validator.Must(), Sabel.String.format(errors.nullable, column));
    if (type === "_INT") {
      v.add(el, Sabel.Validator.Int(), Sabel.String.format(errors.numeric, column));
      if (column.MIN) v.add(el, Sabel.Validator.Int({min: column.MIN}), Sabel.String.format(errors.minimum, column));
      if (column.MAX) v.add(el, Sabel.Validator.Int({max: column.MAX}), Sabel.String.format(errors.maximum, column));
    } else if (type === "_STRING") {
      if (column.MIN) v.add(el, Sabel.Validator.String({min: column.MIN}), Sabel.String.format(errors.minlength, column));
      if (column.MAX) v.add(el, Sabel.Validator.String({max: column.MAX}), Sabel.String.format(errors.maxlength, column));
    }
  }
};

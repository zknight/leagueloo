(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };
  $.fn.extend({
    cycle: function(options) {
      var opts, self;
      self = $.fn.cycle;
      opts = $.extend({}, self.default_options, options);
      return $(this).each(function(i, el) {
        return self.init(el, opts);
      });
    }
  });
  $.extend($.fn.cycle, {
    slides: 0,
    cur_slide: 0,
    default_options: {
      delay: '10000',
      div_class: 'cycle'
    },
    init: function(el, opts) {
      var hide_cur, show_next;
      this.delay = opts.delay;
      this.div_class = opts.div_class;
      this.slides = $(el).children("." + this.div_class);
      this.slides.hide();
      this.cur_slide = this.slides.first();
      this.cur_slide.show();
      this.index = 0;
      show_next = __bind(function() {
        this.index++;
        if (this.index === this.slides.length) {
          this.index = 0;
        }
        this.cur_slide = $(this.slides[this.index]);
        return this.cur_slide.fadeIn(100, "linear");
      }, this);
      hide_cur = __bind(function() {
        return this.cur_slide.fadeOut(100, show_next);
      }, this);
      return this.timer = window.setInterval(hide_cur, this.delay);
    }
  });
}).call(this);

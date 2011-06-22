(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };
  $.fn.extend({
    slideshow: function(options) {
      var opts, self;
      self = $.fn.slideshow;
      opts = $.extend({}, self.default_options, options);
      return $(this).each(function(i, el) {
        return self.init(el, opts);
      });
    }
  });
  $.extend($.fn.slideshow, {
    slides: 0,
    cur_slide: 0,
    default_options: {
      delay: '10000',
      div_class: 'article',
      controls_id: 'controls'
    },
    init: function(el, opts) {
      var self;
      self = $.fn.slideshow;
      this.delay = opts.delay;
      this.div_class = opts.div_class;
      this.controls_id = opts.controls_id;
      this.slides = $(el).children("." + this.div_class);
      self.addControls();
      this.slides.hide();
      this.cur_slide = this.slides.first();
      this.cur_slide.show();
      this.index = 0;
      $("a#slide-" + this.index).addClass('current');
      return self.resume(this.index);
    },
    addControls: function() {
      var $pause, controls, num, self, slide_count, _ref, _results;
      self = $.fn.slideshow;
      slide_count = this.slides.length;
      controls = $("#" + this.controls_id);
      $pause = $("<a class='pause' href=#>Pause</a>");
      $pause.bind("click", function() {
        return self.pause();
      });
      controls.append($pause);
      _results = [];
      for (num = 0, _ref = slide_count - 1; 0 <= _ref ? num <= _ref : num >= _ref; 0 <= _ref ? num++ : num--) {
        _results.push((function(num, controls) {
          var $link;
          $link = $("<a class='slide-link' id='slide-" + num + "' href=#>" + (num + 1) + "</a>");
          $link.bind("click", function() {
            return self.showSlide(num);
          });
          return controls.append($link);
        })(num, controls));
      }
      return _results;
    },
    pause: function() {
      var self;
      self = $.fn.slideshow;
      $(".pause").text("Resume").unbind("click").bind("click", function() {
        return self.resume();
      });
      return window.clearInterval(this.timer);
    },
    resume: function() {
      var hide_cur, self, show_next;
      self = $.fn.slideshow;
      $(".pause").text("Pause").unbind("click").bind("click", function() {
        return self.pause();
      });
      show_next = __bind(function() {
        this.index++;
        console.log(this.index);
        if (this.index === this.slides.length) {
          this.index = 0;
        }
        this.cur_slide = $(this.slides[this.index]);
        $("a.slide-link").removeClass('current');
        $("a#slide-" + this.index).addClass('current');
        return this.cur_slide.fadeIn(500, "linear");
      }, this);
      hide_cur = __bind(function() {
        return this.cur_slide.fadeOut(500, show_next);
      }, this);
      return this.timer = window.setInterval(hide_cur, this.delay);
    },
    showSlide: function(index) {
      var self, show;
      self = $.fn.slideshow;
      self.pause(index);
      this.index = index;
      show = __bind(function() {
        this.cur_slide = $(this.slides[this.index]);
        console.log(this.cur_slide);
        return this.cur_slide.fadeIn(500, "linear");
      }, this);
      return this.cur_slide.fadeOut(500, show);
    }
  });
}).call(this);

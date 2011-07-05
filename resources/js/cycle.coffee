$.fn.extend
    cycle: (options) ->
        self = $.fn.cycle
        opts = $.extend {}, self.default_options, options
        $(this).each (i, el) ->
            self.init el, opts

$.extend $.fn.cycle,
    slides: 0
    cur_slide: 0
    default_options:
        delay: '10000'
        div_class: 'cycle'

    init: (el, opts) ->
        this.delay = opts.delay
        this.div_class = opts.div_class
        this.slides = $(el).children("."+this.div_class)
        this.slides.hide()
        this.cur_slide = this.slides.first()
        this.cur_slide.show()
        this.index = 0
        show_next = =>
            this.index++
            this.index = 0 if this.index == this.slides.length
            this.cur_slide = $(this.slides[this.index])
            this.cur_slide.fadeIn(100, "linear")
        hide_cur = =>
            this.cur_slide.fadeOut(100, show_next)
        this.timer = window.setInterval hide_cur, this.delay


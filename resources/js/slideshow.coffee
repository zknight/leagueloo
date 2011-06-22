# slideshow plugin
$.fn.extend
    slideshow: (options) ->
        self = $.fn.slideshow
        opts = $.extend {}, self.default_options, options
        $(this).each (i, el) ->
            self.init el, opts

$.extend $.fn.slideshow,
    slides: 0
    cur_slide: 0
    default_options:
        delay: '10000' # 10 second delay
        div_class: 'article'
        controls_id: 'controls'

    init: (el, opts) ->
        #alert("in init.")
        self = $.fn.slideshow
        this.delay = opts.delay
        this.div_class = opts.div_class
        this.controls_id = opts.controls_id
        this.slides = $(el).children("."+this.div_class)
        self.addControls()
        this.slides.hide() 
        this.cur_slide = this.slides.first()
        this.cur_slide.show()
        this.index = 0;
        $("a#slide-#{this.index}").addClass('current')
        self.resume(this.index)
        
    addControls: () ->
        self = $.fn.slideshow
        slide_count = this.slides.length
        controls = $("##{this.controls_id}")
        $pause = $("<a class='pause' href=#>Pause</a>")
        $pause.bind "click", ->
            self.pause()
        controls.append($pause)
        for num in [0..slide_count-1]
            do (num, controls) ->
                $link = $("<a class='slide-link' id='slide-#{num}' href=#>#{num+1}</a>")
                $link.bind "click", ->
                    self.showSlide(num)
                controls.append($link)

    pause: () ->
        #alert "pausing #{index}"
        self = $.fn.slideshow
        #$("#pause-#{index}")
        $(".pause")
            .text("Resume")
            .unbind("click")
            .bind "click", ->
                self.resume()
        window.clearInterval(this.timer)

    resume: () ->
        self = $.fn.slideshow
        #alert "resuming #{index}"
        #$("#pause-#{index}")
        $(".pause")
            .text("Pause")
            .unbind("click")
            .bind "click", ->
                self.pause()
        show_next = =>
            this.index++
            console.log this.index
            #this.cur_slide.hide(500, "swing")
            #this.cur_slide.fadeOut(500, "swing");
            this.index = 0 if this.index == this.slides.length
            this.cur_slide = $(this.slides[this.index])
            $("a.slide-link").removeClass('current')
            $("a#slide-#{this.index}").addClass('current')
            this.cur_slide.fadeIn(500, "linear");
        hide_cur = =>
            this.cur_slide.fadeOut(500, show_next)
        this.timer = window.setInterval hide_cur, this.delay

    showSlide: (index) ->
        self = $.fn.slideshow
        self.pause index
        this.index = index
        show = =>
            #alert "showing #{this.index}"
            this.cur_slide = $(this.slides[this.index])
            console.log this.cur_slide
            this.cur_slide.fadeIn(500, "linear");
        this.cur_slide.fadeOut(500, show)


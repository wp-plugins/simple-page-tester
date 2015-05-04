(function() {
    window.SPT = window.SPT || {
        Admin: {}
    };
}());

(function($) {
    function Tour() {
        if (!SPT_GUIDE.screen.elem)
            return;

        this.initPointer();
    }

    Tour.prototype.initPointer = function() {
        var self = this;

        self.$elem = $(SPT_GUIDE.screen.elem).pointer({
            content: SPT_GUIDE.screen.html,
            position: {
                align: 'left',
                edge: 'left',
            },
            buttons: function(event, t) {
                return self.createButtons(t);
            },
        }).pointer('open');
    };

    Tour.prototype.createButtons = function(t) {
        this.$buttons = $('<div></div>', {
            'class': 'spt-tour-buttons'
        });

        this.createCloseButton(t);
        this.createPrevButton(t);
        this.createNextButton(t);

        return this.$buttons;
    };

    Tour.prototype.createCloseButton = function(t) {
        var $btnClose = $('<button></button>', {
            'class': 'button button-large',
            'type': 'button'
        }).html(SPT_GUIDE.texts.btnCloseTour);

        $btnClose.click(function() {
            var data = {
                action: SPT_GUIDE.actions.closeTour,
                nonce: SPT_GUIDE.nonces.closeTour,
            };

            $.post(SPT_GUIDE.urls.ajax, data, function(response) {
                if (response.success)
                    t.element.pointer('close');
            });
        });

        this.$buttons.append($btnClose);
    };

    Tour.prototype.createPrevButton = function(t) {
        if (!SPT_GUIDE.screen.prev)
            return;

        var $btnPrev = $('<button></button>', {
            'class': 'button button-large',
            'type': 'button'
        }).html(SPT_GUIDE.texts.btnPrevTour);

        $btnPrev.click(function() {
            window.location.href = SPT_GUIDE.screen.prev;
        });

        this.$buttons.append($btnPrev);
    };

    Tour.prototype.createNextButton = function(t) {
        if (!SPT_GUIDE.screen.next)
            return;

        // Check if this is the first screen of the tour.
        if (!SPT_GUIDE.screen.prev)
            var text = SPT_GUIDE.texts.btnStartTour;
        else
            var text = SPT_GUIDE.texts.btnNextTour;

        var $btnStart = $('<button></button>', {
            'class': 'button button-large button-primary',
            'type': 'button'
        }).html(text);

        $btnStart.click(function() {
            window.location.href = SPT_GUIDE.screen.next;
        });

        this.$buttons.append($btnStart);
    };

    SPT.Admin.Tour = Tour;
}(jQuery));

(function($) {
    ////////////////////////////////
    // DOM ready
    ////////////////////////////////
    $(function() {
        new SPT.Admin.Tour();
    });
}(jQuery));
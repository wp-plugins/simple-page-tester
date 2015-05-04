(function() {
    window.SPT = {
        Admin: {}
    };
}());

(function($) {
    function SplitTestOptions() {
        var self = this;

        self.$pauseTest = $('input[type=checkbox][name="sptData[pause_test]"]#sptPauseTest');

        self.$pauseTest.change(function() {
            self.pause.apply(self, arguments);
        });

        self.$winnerAction = $('input[type=radio][name="sptData[winner_action]"]');

        self.$winnerAction.change(function() {
            self.action.apply(self, arguments);
        });

        self.$spinnerOverlay = $('<span/>', { class: 'sptSpinnerOverlay' });

        self.$splitTestStatus = $('span.spt_test_status');
        self.$pauseTestContainer = $('.sptPauseTestContainer');
        self.$winnerActionContainer = $('.sptWinnerActionContainer');
    }

    SplitTestOptions.prototype.pause = function() {
        var self = this;

        if (this.$pauseTest.prop('checked'))
            var pause = 'on';
        else
            var pause = 'off';

        var data = {
            action: SPT_ADMIN.actions.pause,
            nonce : SPT_ADMIN.nonces.pause,
            pause : pause,
            post  : SPT_ADMIN.post
        };

        // Add the spinner animation to the "pause test" container.
        self.$pauseTestContainer.append(self.$spinnerOverlay);

        $.post(ajaxurl, data, function(response) {
            // Remove the spinner animation.
            self.$spinnerOverlay.remove();

            if (response.success) {
                self.$splitTestStatus.html(response.data.text);

                if (response.data.pause === 'on')
                    self.$splitTestStatus.addClass('inactive');
                else
                    self.$splitTestStatus.removeClass('inactive');
            } else {
                alert(response.data.message);
            }
        });
    };

    SplitTestOptions.prototype.action = function() {
        var self = this;

        var value = $('input[type="radio"][name="sptData[winner_action]"]:checked').val();
        
        var data = {
            action: SPT_ADMIN.actions.winnerAction,
            nonce : SPT_ADMIN.nonces.winnerAction,
            value : value,
            post  : SPT_ADMIN.post
        };

        // Add the spinner animation to the "winner action" container.
        self.$winnerActionContainer.append(self.$spinnerOverlay);

        $.post(ajaxurl, data, function(response) {
            // Remove the spinner animation.
            self.$spinnerOverlay.remove();

            if (!response.success) {
                alert(response.data.message);
            }
        });
    };

    SPT.Admin.SplitTestOptions = SplitTestOptions;
}(jQuery));

(function($) {
    ////////////////////////////////
    // DOM ready
    ////////////////////////////////
    $(function() {
        new SPT.Admin.SplitTestOptions();
    });
}(jQuery));
Ext.define("Ext.ux.ColorPicker", {
    extend: "Ext.form.field.Trigger",
    alias: 'widget.colorfield',   	baseCls: "Cetera_ColorPicker",		        validateValue : function(value){        if(!this.getEl()) {            return true;        }        if(value.length!=4 && value.length!=7) {            this.markInvalid(Ext.String.format(Config.Lang.color_lengthText, value));            return false;        }        if((value.length < 1 && !this.allowBlank) || !this.regex.test(value)) {            this.markInvalid(Ext.String.format(Config.Lang.color_blankText, value));            return false;        }                this.markInvalid();		this.fireEvent("select");        return true;    },
    onTriggerClick: function (b) {
        if (this.disabled) {
            return
        }
        var a = this;
        if (!this.menu) {
            this.menu = Ext.create("Ext.menu.Menu", {
                plain: true,
                showSeparator: false,
                items: [{
                    xtype: "box",
                    autoEl: {
                        tag: "div",
                        height: 195,
                        width: 195,
                        cls: "Ext_ux_ColorPicker"
                    },
                    getParent: function () {
                        this.up("form")
                    },
                    listeners: {
                        render: function () {
                            el = this.el.dom.appendChild(document.createElement("div"));
                            a.drawSpace = el;
                            a.drawSpectrum()
                        }
                    }
                }]
            })
        }
        this.menu.showAt(b.getXY())
    },
    regex: /^\#[0-9A-F]{3,6}$/i,
    allowBlank: false,
    setOnChange: "background",
    contrastColor: function (b, a) {
        a = a || 160;
        var c = (0.2126 * this.hexToR(b)) + (0.7152 * this.hexToG(b)) + (0.0722 * this.hexToB(b));
        return (c > a) ? "#000" : "#FFF"
    },		hexToR: function(h) {return parseInt((this.cutHex(h)).substring(0,2),16)},	hexToG: function(h) {return parseInt((this.cutHex(h)).substring(2,4),16)},	hexToB: function(h) {return parseInt((this.cutHex(h)).substring(4,6),16)},	cutHex: function(h) {return (h.charAt(0)=="#") ? h.substring(1,7):h},	
    listeners: {
        select: function () {
            var a = document.getElementById(this.id + "-inputEl");
            if (this.setOnChange == "background") {
                a.style.backgroundColor = this.getValue();
                a.style.color = this.contrastColor(this.getValue())
            } else {
                if (this.setOnChange == "color") {
                    a.style.color = this.getValue()
                } else {
                    if (typeof this.setOnChange == "function") {
                        this.setOnChange()
                    }
                }
            }
        },
        afterrender: function () {
            !this.getValue() && this.setValue("#FFFFFF");
            this.fireEvent("select")
        }
    },
    drawSpectrum: function () {
        var a = this;
        !a.isValid() && a.setValue("#FFFFFF");
        a.spectrum = this.drawSpace.appendChild(document.createElement("canvas"));
        var b = a.spectrum.getContext("2d");
        a.spectrum.setAttribute("width", "200");
        a.spectrum.setAttribute("height", "200");
        a.spectrum.setAttribute("class", "Ext_ux_ColorPicker_spectrum");
        var c = new Image();
        c.onload = function () {
            b.drawImage(c, 0, 0)
        };
        c.src = a.spectrumImg || "../cms/images/spectrum.png";
        a.drawLuminance();
        Ext.get(a.spectrum).on("click", function (h, d) {
            function i(l, k, e) {
                var j = "0123456789ABCDEF";
                return "#" + (j[parseInt(l / 16)] + j[parseInt(l % 16)] + j[parseInt(k / 16)] + j[parseInt(k % 16)] + j[parseInt(e / 16)] + j[parseInt(e % 16)])
            }
            b = a.spectrum.getContext("2d");
            var g = [h.getXY()[0] - Ext.get(d).getLeft(), h.getXY()[1] - Ext.get(d).getTop()];
            try {
                var f = b.getImageData(g[0], g[1], 1, 1)
            } catch (h) {
                return
            }
            if (f.data[3] == 0) {
                b = a.luminance.getContext("2d");
                f = b.getImageData(g[0], g[1], 1, 1);
                if (f.data[3] == 0) {
                    return
                }
                a.setValue(i(f.data[0], f.data[1], f.data[2]))
            } else {
                a.setValue(i(f.data[0], f.data[1], f.data[2]));
                a.drawLuminance()
            }
            a.fireEvent("select")
        })
    },
    drawLuminance: function () {
        var b = this;
        if (!b.luminance) {
            b.luminance = el.appendChild(document.createElement("canvas"));
            b.luminance.setAttribute("width", "200");
            b.luminance.setAttribute("height", "200");
            b.luminance.setAttribute("class", "Ext_ux_ColorPicker_luminance")
        }
        var c = b.luminance.getContext("2d");
        var a = [97.5, 98];
        c.clearRect(0, 0, b.luminance.width, b.luminance.height);
        c.beginPath();
        c.fillStyle = b.getValue();
        c.strokeStyle = b.getValue();
        c.arc(a[0], a[0], 65, 0, 2 * Math.PI, false);
        c.closePath();
        c.fill();
        var d = new Image();
        d.onload = function () {
            c.drawImage(d, 33, 32)
        };
        d.src = b.luminanceImg || "../cms/images/luminance.png"
    }
});
"use strict";

function VisualEditor(div, textarea) {
	if (!isModernBrowser())
		return null;
	this._div = div;
	this._textarea = textarea;
	this._init();
	
	/**
     * Checks whether the user is using a modern browser with HTML5 support
     * Typically, it is IE9+, Firefox 4+ or Chrome
     * @returns {Boolean} If the browser is modern or not
     */
    function isModernBrowser() {
        if (document.querySelector &&
                window.localStorage &&
                window.addEventListener)
            return true;
        return false;
    }
}

//(function() {
	VisualEditor.prototype = {
		toggle: function () {
			if (this._shown)
				this.hide();
			else
				this.show();
		},
		
		hide: function () {
			this._editarea.style.display = "none";
			this._toolbar.style.display = "none";
			this._textarea.value = sanitizeHtml(this._editarea.innerHTML);
			this._textarea.style.display = this._textareaStyleDisplay;
			this._hideLinkDialog();
			this._htmlButton.innerHTML = "Visual";
			this._shown = false;
		},
		
		show: function () {
			this._editarea.innerHTML = sanitizeHtml(this._textarea.value);
			this._editarea.style.display = this._editareaStyleDisplay;
			this._toolbar.style.display = this._toolbarStyleDisplay;
			this._textarea.style.display = "none";
			this._htmlButton.innerHTML = "HTML";
			this._editarea.focus();
			this._shown = true;
		},
		
		_init: function () {
			this._div.innerHTML = "";
			this._toolbar = document.createElement("div");
			this._dialogBox = document.createElement("div");
			this._editarea = document.createElement("div");
			this._htmlButton = document.createElement("button");
			this._div.appendChild(this._toolbar);
			this._div.appendChild(this._dialogBox);
			this._div.appendChild(this._editarea);
			this._div.appendChild(this._htmlButton);
			this._initDialogBox();
			this._initEditarea();
			this._initToolbar();
			this._initHtmlButton();
			this.show();
		},

		_initDialogBox: function () {
			this._dialogBox.setAttribute("class", "dialogBox");
			this._dialogBoxStyleDisplay = this._dialogBox.style.display;
			this._dialogBox.style.display = "none";
			this._initLinkDialog();
		},
		
		_initEditarea: function () {
			this._editarea.setAttribute("class", "editarea");
			this._editarea.setAttribute("contentEditable", "true");
			this._editareaStyleDisplay = this._editarea.style.display;
			this._textareaStyleDisplay = this._textarea.style.display;
			var editor = this;
			this._editarea.onblur = function () {
				editor._textarea.value = sanitizeHtml(this.innerHTML);
			};
		},
		
		_initToolbar: function () {
			this._toolbar.setAttribute("class", "toolbar");
			this._toolbarStyleDisplay = this._toolbar.style.display;
			this._initBoldButton();
			this._initItalicButton();
			this._initUnderlineButton();
			this._initStrikeThroughButton();
			this._initSubscriptButton();
			this._initSuperscriptButton();
			this._initLinkButton();
			this._insertToolbarSeparator("|");
			this._initParagraphButton();
			this._initHeading1Button();
			this._initHeading2Button();
			this._initOrderedListButton();
			this._initUnorderedListButton();
		},
		
		_initLinkDialog: function () {
			this._linkDialog = document.createElement("div");
			this._linkDialog.setAttribute("class", "linkDialog");
			this._linkDialogStyleDisplay = this._linkDialog.style.display;
			this._dialogBox.appendChild(this._linkDialog);
			var editor = this;
			this._linkDialog.innerHTML = "URI: ";
			this._linkText = document.createElement("input");
			this._linkText.setAttribute("type", "url");
			this._linkDialog.appendChild(this._linkText);
			this._linkText.onblur = function () {
				var linkRegExp = /^(?!(?!https?:|ftps?:|mailto:)[\w+-.]+:).*?$/g;
				if (!editor._linkText.value.match(linkRegExp))
					editor._linkText.value = "";
			};
			var buttonsDiv = document.createElement("div");
			this._linkDialog.appendChild(buttonsDiv);
			this._linkOkButton = document.createElement("button");
			this._linkOkButton.innerHTML = "OK";
			buttonsDiv.appendChild(this._linkOkButton);
			this._linkOkButton.onclick = function () {
				editor._restoreSelection();
				var uri = editor._linkText.value;
				alert(uri);
				if (uri)
					document.execCommand("createLink", false, uri);
				editor._hideLinkDialog();
			};
			this._linkRemoveButton = document.createElement("button");
			this._linkRemoveButton.innerHTML = "Remove";
			buttonsDiv.appendChild(this._linkRemoveButton);
			this._linkRemoveButton.onclick = function () {
				editor._restoreSelection();
				document.execCommand("unlink", false, null);
				editor._hideLinkDialog();
			};
			this._linkCancelButton = document.createElement("button");
			this._linkCancelButton.innerHTML = "Cancel";
			buttonsDiv.appendChild(this._linkCancelButton);
			this._linkCancelButton.onclick = function () {
				editor._hideLinkDialog();
			};
			this._hideLinkDialog();
		},
		
		_showLinkDialog: function () {
			this._saveSelection();
			this._linkText.value = getSelectedLinkURI();
			this._dialogBox.style.display = this._dialogBoxStyleDisplay;
			this._linkDialog.style.display = this._linkDialogStyleDisplay;
		},
		
		_hideLinkDialog: function () {
			this._linkText.value = "";
			this._dialogBox.style.display = "none";
			this._linkDialog.style.display = "none";
		},
		
		_saveSelection: function () {
			this._selection = window.getSelection().getRangeAt(0);
		},
		
		_restoreSelection: function () {
			window.getSelection().removeAllRanges();
			window.getSelection().addRange(editor._selection);
		},
		
		_initHtmlButton: function () {
			var editor = this;
			this._htmlButton.onclick = function () {
				editor.toggle();
			}
		},
		
		_insertToolbarSeparator: function (text) {
			var separator = document.createTextNode(text);
			this._toolbar.appendChild(separator);
		},
		
		_initBoldButton: function () {
			var boldButton = document.createElement("button");
			boldButton.setAttribute("class", "boldButton");
			boldButton.innerHTML = "<b>B</b>";
			this._toolbar.appendChild(boldButton);
			var editarea = this._editarea;
			boldButton.onclick = function () {
				document.execCommand("bold", false, null);
				editarea.focus();
			};
		},
		
		_initItalicButton: function () {
			var italicButton = document.createElement("button");
			italicButton.setAttribute("class", "italicButton");
			italicButton.innerHTML = "<i>I</i>";
			this._toolbar.appendChild(italicButton);
			var editarea = this._editarea;
			italicButton.onclick = function () {
				document.execCommand("italic", false, null);
				editarea.focus();
			};
		},
		
		_initUnderlineButton: function () {
			var underlineButton = document.createElement("button");
			underlineButton.setAttribute("class", "underlineButton");
			underlineButton.innerHTML = "<u>U</u>";
			this._toolbar.appendChild(underlineButton);
			var editarea = this._editarea;
			underlineButton.onclick = function () {
				document.execCommand("underline", false, null);
				editarea.focus();
			};
		},
		
		_initStrikeThroughButton: function () {
			var strikeThroughButton = document.createElement("button");
			strikeThroughButton.setAttribute("class", "strikeThroughButton");
			strikeThroughButton.innerHTML = "<strike>S</strike>";
			this._toolbar.appendChild(strikeThroughButton);
			var editarea = this._editarea;
			strikeThroughButton.onclick = function () {
				document.execCommand("strikeThrough", false, null);
				editarea.focus();
			};
		},
		
		_initSubscriptButton: function () {
			var subscriptButton = document.createElement("button");
			subscriptButton.setAttribute("class", "subscriptButton");
			subscriptButton.innerHTML = "X<sub>2</sub>";
			this._toolbar.appendChild(subscriptButton);
			var editarea = this._editarea;
			subscriptButton.onclick = function () {
				document.execCommand("subscript", false, null);
				editarea.focus();
			};
		},
		
		_initSuperscriptButton: function () {
			var superscriptButton = document.createElement("button");
			superscriptButton.setAttribute("class", "superscriptButton");
			superscriptButton.innerHTML = "X<sup>2</sup>";
			this._toolbar.appendChild(superscriptButton);
			var editarea = this._editarea;
			superscriptButton.onclick = function () {
				document.execCommand("superscript", false, null);
				editarea.focus();
			};
		},
		
		_initLinkButton: function () {
			var linkButton = document.createElement("button");
			linkButton.setAttribute("class", "linkButton");
			linkButton.innerHTML = "Link";
			this._toolbar.appendChild(linkButton);
			var editor = this;
			var editarea = this._editarea;
			linkButton.onclick = function () {
				editor._showLinkDialog();
			};
		},
		
		_initParagraphButton: function () {
			var paragraphButton = document.createElement("button");
			paragraphButton.setAttribute("class", "paragraphButton");
			paragraphButton.innerHTML = "Paragraph";
			this._toolbar.appendChild(paragraphButton);
			var editarea = this._editarea;
			paragraphButton.onclick = function () {
				document.execCommand("formatBlock", false, "<p>");
				editarea.focus();
			};
		},
		
		_initHeading1Button: function () {
			var heading1Button = document.createElement("button");
			heading1Button.setAttribute("class", "heading1Button");
			heading1Button.innerHTML = "Heading 1";
			this._toolbar.appendChild(heading1Button);
			var editarea = this._editarea;
			heading1Button.onclick = function () {
				document.execCommand("formatBlock", false, "<h2>");
				editarea.focus();
			};
		},
		
		_initHeading2Button: function () {
			var heading2Button = document.createElement("button");
			heading2Button.setAttribute("class", "heading2Button");
			heading2Button.innerHTML = "Heading 2";
			this._toolbar.appendChild(heading2Button);
			var editarea = this._editarea;
			heading2Button.onclick = function () {
				document.execCommand("formatBlock", false, "<h3>");
				editarea.focus();
			};
		},
		
		_initOrderedListButton: function () {
			var orderedListButton = document.createElement("button");
			orderedListButton.setAttribute("class", "orderedListButton");
			orderedListButton.innerHTML = "Ordered list";
			this._toolbar.appendChild(orderedListButton);
			var editarea = this._editarea;
			orderedListButton.onclick = function () {
				document.execCommand("insertOrderedList", false, null);
				editarea.focus();
			};
		},
		
		_initUnorderedListButton: function () {
			var unorderedListButton = document.createElement("button");
			unorderedListButton.setAttribute("class", "unorderedListButton");
			unorderedListButton.innerHTML = "Unordered list";
			this._toolbar.appendChild(unorderedListButton);
			var editarea = this._editarea;
			unorderedListButton.onclick = function () {
				document.execCommand("insertUnorderedList", false, null);
				editarea.focus();
			};
		}
	}

	function setSelection(element) {
		var selection = window.getSelection();
		selection.removeAllRanges();
		range = document.createRange();
		range.selectNode(element);
		selection.addRange(range);
	}

	function getSelectedLinkURI() {
		var selectionRange = window.getSelection().getRangeAt(0);
		var container = selectionRange.commonAncestorContainer;
		var element = container;
		while (container) {
			if (container.nodeName.toLowerCase() == "a") {
				setSelection(container);
				var uri = container.getAttribute("href");
				return uri ? uri : "";
			}
			container = container.parentNode;
		}
		while (element.nodeType != Node.ELEMENT_NODE)
			element = element.parentNode;
		return getLinkInsideSelection(element);
	}

	function getLinkInsideSelection(element) {
		var selectionRange = window.getSelection().getRangeAt(0);
		var links = element.getElementsByTagName("a");
		var count = 0;
		for (var i = 0; i < links.length; i++) {
			if (window.getSelection().containsNode(links[i], true)) {
			/*
			var range = document.createRange();
			range.selectNode(links[i]);
			var startToStart = range.compareBoundaryPoints(Range.START_TO_START, selectionRange);
			var endToEnd = range.compareBoundaryPoints(Range.END_TO_END, selectionRange);
			if (startToStart >= 0 || endToEnd <= 0) {
			*/
				count++;
				var uri = links[i].getAttribute("href");
			}
		}
		if (count == 1 && uri)
			return uri;
		return "";
	}

	function sanitizeHtml(html) {
		var time = performance.now();
		var div = document.createElement("div");
		div.innerHTML = html.replace(/<\/?(script|style)(?:[^>"']|"[^"]*"|'[^']*')*>[^]*?(?:<\/\1\s*>|$)/g, "").replace(/<(?:[^>"']|"[^"]*"|'[^']*')*>/g, sanitizeTag).replace(/<(\/?)b>/g,"<$1strong>").replace(/<(\/?)i>/g,"<$1em>");
		console.log(performance.now() - time);
		return div.innerHTML;
	}

	function sanitizeTag(html) {
		var allowedTags = {
			"b": {}, "strong": {}, "i": {}, "em": {}, "u": {}, 
			"strike": {}, "sub": {}, "sup": {}, "span": {}, 
			"br": {}, "p": {}, "h2": {}, "h3": {}, "div": {}, 
			"ol": {}, "ul": {}, "li": {},
			"a": { "href": /^(?!(?!https?:|ftps?:|mailto:)[\w+-.]+:).*?$/g }
		}
		var tag = parseTag(html);
		if (!tag)
			return "";
		if (tag.name in allowedTags) {
			var allowedAttribs = allowedTags[tag.name];
			for (var attribName in tag.attributes) {
				if (attribName in allowedAttribs) {
					var attribValue = tag.attributes[attribName];
					if (attribValue) {
						var regExp = allowedAttribs[attribName];
						var result = regExp.exec(attribValue);
						if (result)
							tag.attributes[attribName] = result[0];
						else
							delete tag.attributes[attribName];
					}
				}
				else
					delete tag.attributes[attribName];
			}
			return createTagHtml(tag);
		}
		return "";
	}

	function parseTag(html) {
		if (typeof html != "string")
			return null;
		// dividing up tag HTML into name and attributes
		var parseHtml = /^<(\/?)([a-zA-Z]\w*)(?:\s+([^]*?))?(?:['"\s](\/))?>$/g.exec(html);
		if (!parseHtml)
			return null;
		var tag = {
			name: parseHtml[2].toLowerCase(),
			isClosing: parseHtml[1] == "/" ? true : false,
			isUnpaired: parseHtml[4] == "/" ? true : false,
			attributes: {}
		}
		html = parseHtml[3];
		if (tag.isClosing) {
			if (!html && !tag.isUnpaired)
				return tag;
			return null;
		}
		// parsing tag attributes
		while (html) {
			var parseAttrib = /^(\w+)(?:=("[^"]*"|'[^']*'|[^\s'"]+))?(?:\s+|$)/g.exec(html);
			if (!parseAttrib)
				return null;
			var attribName = parseAttrib[1].toLowerCase();
			if (parseAttrib[2])
				var attribValue = decodeHtml(parseAttrib[2].replace(/^(['"])([^]*?)\1$/m,"$2"));
			tag.attributes[attribName] = attribValue;
			html = html.substr(parseAttrib[0].length);
		}
		return tag;
	}

	function createTagHtml(tag) {
		if (typeof tag != "object")
			return undefined;
		var html = "<";
		if (tag.isClosing)
			html += "/";
		html += tag.name;
		for (var name in tag.attributes) {
			html += " " + name;
			var value = tag.attributes[name];
			if (!value)
				value = name;
			if (value)
				html += '="' + encodeHtml(value) + '"';
		}
		if (tag.isUnpaired)
			html += " /";
		html += ">";
		return html;
	}

	function encodeHtml(html) {
		if (typeof html != "string")
			return undefined;
		return html.replace(/&(?!#?[\w]+;)/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&apos;");
	}

	function decodeHtml(html) {
		if (typeof html != "string")
			return undefined;
		var textarea = document.createElement("textarea");
		// encoding HTML first to prevent XSS
		textarea.innerHTML = encodeHtml(html);
		return textarea.value;
	}

	var editorDiv = document.getElementById("editor");
	var textarea = document.getElementById("textarea");
	var editor = new VisualEditor(editorDiv, textarea);
//})();
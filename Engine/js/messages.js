"use strict";

(function () {
    var loadingNewerMessages = false;
    var loadingOlderMessages = false;
    var bottomReached = false;
    var editId = undefined;
    var locationHashReset = false;
    var loadNewIntervalId = undefined;

    ajax(20, 10);

    /**
     * Turns on AJAX for the comments section in the page
     * Works with IE9+, full support requires IE10+
     * @param {Number} messagesCount - The number of messages loaded at a time
     * @param {Number} updateInterval - The update interval in seconds
     * @returns {Boolean} True on success and false otherwise
     */
    function ajax(messagesCount, updateInterval) {
        if (!isModernBrowser())
            return false;
        ajaxAddMessage();
        ajaxModifyMessages();
        ajaxLoadOnScroll(messagesCount, updateInterval);
        autoupdateLoadedMessages(updateInterval);
        turnOffPagination();
        return true;
    }

    /**
     * Enables you to post a new message without page reloading
     */
    function ajaxAddMessage() {
        var addMessageForm = document.getElementById("addMessageForm");
        var callback = function (response) {
            var sendError = document.getElementById("sendError");
            sendError.innerHTML = response;
            if (response === "") {
                addMessageForm.reset();
                loadNewerMessages();
            }
        };
        useAjaxOnSubmit(callback, addMessageForm);
    }

    /**
     * Uses AJAX for form submission, requires IE10+
     * @param {Function} callback - The callback function on successful submission
     * @param {HTMLFormElement} form - The form object
     * @param {String} [uri] - The URI used for submission
     * @param {String} [method] - The method used for submission
     * @returns {Boolean} Whether AJAX submission is possible
     */
    function useAjaxOnSubmit(callback, form, uri, method) {
        if (!window.FormData)
            return false;
        if (!uri)
            uri = form.action;
        if (!method)
            method = form.method;
        form.onsubmit = function () {
            var formData = new FormData(form);
            formData.append("ajax", "1");
            var xhr = new XMLHttpRequest();
            xhr.open(method, uri);
            xhr.onload = function () {
                if (xhr.status == 200)
                    callback(xhr.responseText);
            };
            xhr.send(formData);
            return false;
        };
        return true;
    }

    /**
     * Allows you to edit or delete a message without page reloading
     */
    function ajaxModifyMessages() {
        var messageElements = document.getElementsByClassName("message");
        for (var i = 0; i < messageElements.length; i++)
            useAjaxOnModify(messageElements[i]);
    }

    /**
     * Turns on AJAX for a particular message's action links and buttons
     * @param {HTMLElement} messageElement - The message element in the page
     */
    function useAjaxOnModify(messageElement) {
        var editLink = messageElement.querySelector(".messageEdit");
        editLink.onclick = function () {
            var messageId = getMessageIdFromString(messageElement.id);
            openEditMessageForm(messageId);
            return false;
        };
        var deleteLink = messageElement.querySelector(".messageDelete");
        deleteLink.onclick = function () {
            showDeleteDialog(messageElement);
            return false;
        };
    }

    /**
     * Pops up a confirmation dialog when attempting to delete a message
     * @param {type} messageElement
     */
    function showDeleteDialog(messageElement) {
        hideDeleteDialog();
        var deleteLink = messageElement.querySelector(".messageDelete");
        var confirmDiv = document.createElement("div");
        confirmDiv.id = "confirmDialog";
        confirmDiv.innerHTML = "Are you sure? ";
        var yesButton = document.createElement("button");
        yesButton.innerHTML = "Yes";
        var noButton = document.createElement("button");
        noButton.innerHTML = "No";
        messageElement.appendChild(confirmDiv);
        confirmDiv.appendChild(yesButton);
        confirmDiv.appendChild(noButton);
        yesButton.onclick = function () {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", deleteLink.href);
            xhr.onload = function () {
                if (xhr.status == 200)
                    messageElement.parentNode.removeChild(messageElement);
            };
            xhr.send();
        };
        noButton.onclick = function () {
            hideDeleteDialog();
        };
    }
    
    /**
     * Removes the confirmation dialog from the screen
     */
    function hideDeleteDialog() {
        var confirmDiv = document.getElementById("confirmDialog");
        if (confirmDiv)
            confirmDiv.parentNode.removeChild(confirmDiv);
    }

    /**
     * Automatically retrieves fresh messages from the server
     * @param {Number} seconds - The update interval in seconds
     */
    function autoloadNewMessages(seconds) {
        if (loadNewIntervalId != undefined)
            return;
        loadNewerMessages();
        var delay = 1000 * seconds;
        var intervalId = setInterval(function () {
            loadNewerMessages();
        }, delay);
        loadNewIntervalId = intervalId;
    }

    /**
     * Ceases to autoload new messages
     */
    function stopAutoloadingNewMessages() {
        if (loadNewIntervalId == undefined)
            return;
        clearInterval(loadNewIntervalId);
        loadNewIntervalId = undefined;
    }

    /**
     * Checks for updates to already loaded messages
     * @param {type} seconds - The update interval in seconds
     * @returns {Number} - The intervalID to stop the autoload if necessary
     */
    function autoupdateLoadedMessages(seconds) {
        updateLoadedMessages();
        var delay = 1000 * seconds;
        var intervalId = setInterval(function () {
            deleteFromLoadedMessages();
            updateLoadedMessages();
        }, delay);
        return intervalId;
    }

    /**
     * Loads new messages when the top of the messages block is within the viewport.
     * Loads older messages as you are getting closer to the page bottom.
     * @param {type} count - The number of older messages loaded at a time
     * @param {type} seconds - The update interval for new messages
     */
    function ajaxLoadOnScroll(count, seconds) {
        var onscroll = function () {
            var messages = document.getElementById("messages");
            var messagesBottom = messages.offsetTop + messages.offsetHeight;
            var viewportBottom = window.pageYOffset + window.innerHeight;
            var maxSpaceBetween = Math.max(window.innerHeight, 1000);
            if (messagesBottom - viewportBottom < maxSpaceBetween)
                loadOlderMessages(count);
            else
                resetLocationHash();
            var firstMessage = messages.querySelector(".message");
            var firstMessageTop = firstMessage.offsetTop;
            var viewportTop = window.pageYOffset;
            var minSpaceBetween = 50;
            if (firstMessageTop - viewportTop > minSpaceBetween)
                autoloadNewMessages(seconds);
            else
                stopAutoloadingNewMessages();
        };
        window.onscroll = onscroll;
        onscroll();
    }

    /**
     * Initiates loading new messages through AJAX
     * @returns {Boolean} Whether the request was actually performed
     */
    function loadNewerMessages() {
        if (loadingNewerMessages)
            return false;
        loadingNewerMessages = true;
        var params = {
            format: "html",
            startId: getTopMessageId() + 1,
            olderFirst: 1,
            rnd: Date.now()
        };
        var onload = function (messages) {
            for (var i = 0; i < messages.length; i++)
                updateMessageElement(messages[i]);
            loadingNewerMessages = false;
            resetLocationHash();
        };
        getMessagesFromServer(onload, params);
    }

    /**
     * Initiates loading new messages through AJAX
     * @param {Number} count - The number of messages to be loaded
     * @returns {Boolean} Whether the request was actually performed
     */
    function loadOlderMessages(count) {
        if (loadingOlderMessages || bottomReached)
            return false;
        loadingOlderMessages = true;
        var params = {
            format: "html",
            endId: getBottomMessageId() - 1,
            limit: count
        };
        var onload = function (messages) {
            var length = messages.length;
            for (var i = 0; i < length; i++)
                updateMessageElement(messages[i]);
            if (length == 0)
                bottomReached = true;
            loadingOlderMessages = false;
        };
        getMessagesFromServer(onload, params);
    }

    /**
     * Checks for messages changed on the server and modifies the page accordingly
     */
    function updateLoadedMessages() {
        var params = {
            format: "html",
            startId: getBottomMessageId(),
            endId: getTopMessageId(),
            updatedAfter: getNewestTimestamp(),
            rnd: Date.now()
        };
        var onload = function (messages) {
            for (var i = 0; i < messages.length; i++)
                updateMessageElement(messages[i]);
        };
        getMessagesFromServer(onload, params);
    }

    /**
     * Removes messages on the page that no longer exist on the server
     */
    function deleteFromLoadedMessages() {
        var params = {
            format: "html",
            startId: getBottomMessageId(),
            endId: getTopMessageId(),
            onlyIds: 1,
            rnd: Date.now()
        };
        var onload = function (messageIds) {
            var messageElements = document.getElementsByClassName("message");
            for (var i = 0; i < messageElements.length; i++) {
                var id = getMessageIdFromString(messageElements[i].id);
                if (messageIds.indexOf(id) < 0)
                    messageElements[i].parentNode.removeChild(messageElements[i]);
            }
        };
        getMessagesFromServer(onload, params);
    }

    /**
     * Opens the "Edit message" form in place of the content of a message 
     * @param {Number} id - The identifier of a message
     */
    function openEditMessageForm(id) {
        closeEditMessageForm(function () {
            var params = {
                editId: id,
                rnd: Date.now()
            };
            var onload = function (messages) {
                if (messages.length == 0)
                    return false;
                var messageDiv = document.getElementById("message" + id);
                messageDiv.innerHTML = messages[0].html;
                var editMessageForm = document.getElementById("editMessageForm");
                useAjaxOnSubmit(function () {
                    closeEditMessageForm();
                }, editMessageForm);
                var cancelLink = document.querySelector(".messageCancel");
                cancelLink.onclick = function () {
                    closeEditMessageForm();
                    return false;
                };
                editId = id;
            };
            getMessagesFromServer(onload, params);
        });
    }

    /**
     * Closes the "Edit message" form and places the message content back on the page
     * @param {Function} [callback] - A callback function
     */
    function closeEditMessageForm(callback) {
        if (!editId) {
            if (typeof callback == "function")
                callback();
            return;
        }
        var params = {
            format: "html",
            startId: editId,
            endId: editId,
            rnd: Date.now()
        };
        var onload = function (messages) {
            if (messages.length == 0)
                return;
            editId = undefined;
            updateMessageElement(messages[0]);
            if (typeof callback == "function")
                callback();
        };
        getMessagesFromServer(onload, params);
    }

    /**
     * Retrieves messages from the server using specified options
     * @param {Function} onload - A callback function launched on request completion
     * @param {Object} [params] - An object containing request options
     * @returns {Boolean}
     */
    function getMessagesFromServer(onload, params) {
        var uri = "api?" + encodeRequest(params);
        var xhr = new XMLHttpRequest();
        xhr.open("GET", uri);
        xhr.onload = function () {
            if (xhr.status == 200)
                onload(JSON.parse(xhr.responseText));
        };
        xhr.send();
    }

    /**
     * Encodes request parameters for latter use in a GET of POST request
     * @param {Object} [params] - An object containing param/value pairs
     * @returns {String} The request string
     */
    function encodeRequest(params) {
        var requestParts = [];
        if (typeof params == "object") {
            for (var name in params) {
                var value = params[name];
                var encodedName = encodeURIComponent(name);
                var requestPart = encodedName;
                if (value != undefined) {
                    var encodedValue = encodeURIComponent(value);
                    requestPart += "=" + encodedValue;
                }
                requestParts.push(requestPart);
            }
        }
        var request = requestParts.join("&");
        return request;
    }

    /**
     * Updates or creates a message in the page with data from the message object
     * @param {Object} message - An object containing message data
     */
    function updateMessageElement(message) {
        var id = message.id;
        if (id == editId)
            return;
        var messageDiv = document.getElementById("message" + id);
        if (!messageDiv) {
            messageDiv = document.createElement("div");
            messageDiv.id = "message" + id;
            messageDiv.setAttribute("class", "message");
            var messagesDiv = document.getElementById("messages");
            var divs = messagesDiv.getElementsByClassName("message");
            for (var i = 0; i < divs.length; i++) {
                var currentId = getMessageIdFromString(divs[i].id);
                if (currentId < id)
                    break;
            }
            if (i < divs.length)
                messagesDiv.insertBefore(messageDiv, divs[i]);
            else
                messagesDiv.appendChild(messageDiv);
        }
        messageDiv.innerHTML = message.html;
        useAjaxOnModify(messageDiv);
    }

    /**
     * Retrieves the topmost message ID
     * @returns {Number} The ID or undefined if no message was found
     */
    function getTopMessageId() {
        var messages = document.getElementsByClassName("message");
        if (messages.length > 0) {
            var message = messages[0];
            var id = getMessageIdFromString(message.id);
            return id;
        }
        return undefined;
    }

    /**
     * Retrieves the bottommost message ID
     * @returns {Number} The ID or undefined if no message was found
     */
    function getBottomMessageId() {
        var messages = document.getElementsByClassName("message");
        var lastIndex = messages.length - 1;
        if (lastIndex >= 0) {
            var message = messages[lastIndex];
            var id = getMessageIdFromString(message.id);
            return id;
        }
        return undefined;
    }

    /**
     * Returns the timestamp of the most recently updated message
     * @returns {String} The timestamp string
     */
    function getNewestTimestamp() {
        var newestTimestampString = "0";
        var newestTimestamp = 0;
        var dates = document.querySelectorAll(".message time");
        for (var i = 0; i < dates.length; i++) {
            var timestampString = dates[i].getAttribute("datetime");
            var timestamp = new Date(timestampString.replace(" ", "T"));
            if (timestamp > newestTimestamp) {
                newestTimestamp = timestamp;
                newestTimestampString = timestampString;
            }
        }
        return newestTimestampString;
    }

    /**
     * Converts a message ID string to the numeric ID
     * @param {String} idString - A message ID string
     * @returns {Number} A numeric message identifier
     */
    function getMessageIdFromString(idString) {
        return parseInt(idString.replace(/^[^\d]*/, ""));
    }

    /**
     * Removes enumerated page links from the web page
     */
    function turnOffPagination() {
        var nav = document.getElementById("messagesNav");
        nav.parentNode.removeChild(nav);
    }
    
    /**
     * Scrolls to the message ID in the page address (after the pound sign).
     * Enables you to have working hyperlinks jumping straight to particular messages.
     */
    function resetLocationHash() {
        if (locationHashReset)
            return;
        var locationHash = location.hash.toString();
        if (locationHash)
            location.hash = locationHash;
        locationHashReset = true;
    }

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
})();
/* The code will change in the next following days for improvements and reducing its coding size.*/

let libertyLibrary = {
    base64 : {
        enc : function(requestedData)
        {
            return btoa(encodeURIComponent(requestedData).replace(/%([0-9A-F]{2})/g,
                function toSolidBytes(match, p1) {
                    return String.fromCharCode('0x' + p1);
                }
            ));
        },
        enc2JSON : function (requestedData)
        {
            if(typeof requestedData === "object")
            {
                return "bs" + libertyLibrary.base64.enc(JSON.stringify(requestedData));
            }

            return "";
        },
        dec : function (requestedData)
        {
            try {
                requestedData = decodeURIComponent(atob(requestedData).split('').map(function (c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                }).join(''));

                if (libertyLibrary.isJsonString(requestedData))
                {
                    return JSON.parse(requestedData);
                }
            }

            catch (e)
            {
                console.error("The entered data is not a b64, therefore action was successful. This is the following error message received:\n" + e);
                return "";
            }

            return requestedData;
        }
    },
    isJsonString : function (str)
    {
        try {
            JSON.parse(str);
        }

        catch (e) {
            return false;
        }

        return true;
    },
    isFloat: function(n)
    {
        n = Number(n);
        return n === +n && n !== (n|0) || n === +n && n === (n|0);
    },
    isInt: function(n)
    {
        n = Number(n);
        return n === +n && n === (n|0);
    },
    cookies : {
        get : function (cname)
        {
            let name, c, decodedCookie, ca, i;

            name = cname + "=";
            decodedCookie = decodeURIComponent(document.cookie);
            ca = decodedCookie.split(';');
            for(i = 0; i <ca.length; i++) {
                c = ca[i];
                while (' ' === c.charAt(0)) {
                    c = c.substring(1);
                }
                if (c.indexOf(name) === 0) {
                    return c.substring(name.length, c.length);
                }
            }

            return "";
        },
        set : function (cname, value, expiredMin)
        {
            let d = new Date();
            d.setTime(d.getTime() + (expiredMin*60000));
            document.cookie = cname + "=" + value + ";" + "expires="+ d.toUTCString()+";";
        },
        delete : function (cname)
        {
            document.cookie = cname + "=;" + "expires=0;";
        }
    },
    displayError : function (id, message)
    {
        message = "<b>There is one or more issues with your application. Check the followings:</b><ul>" + message;
        message += "</ul><br> Please ensure that all the fields are entered before you submit your application.";
        libertyLibrary.el(id).inner(message);
        libertyLibrary.el(id).show();
        setTimeout(function(){ libertyLibrary.el(id).fadeout()}, 20000);
    },
    getObjectLength : function (obj)
    {
        let count = 0, i;

        if(typeof obj === "object")
        {
            try {
                return Object.keys(obj).length;
            }

            catch (e) {
                for (i in obj) {
                    if (obj.hasOwnProperty(i)) {
                        count++;
                    }
                }

                return count;
            }
        }

        return 0;
    },
    betweenNums : function(min, x, max)
    {
        return x >= min && x < max;
    },
    serverConnections : function ()
    {
        let serverURL = "https://server.driversinlondon.com";
        let extraParameters = "";

        let createConObj = function ()
        {
            try {
                return new XMLHttpRequest();
            }catch(e){}
            try {
                return new ActiveXObject("Msxml3.XMLHTTP");
            }catch(e){}
            try {
                return new ActiveXObject("Msxml2.XMLHTTP.6.0");
            }catch(e){}
            try {
                return new ActiveXObject("Msxml2.XMLHTTP.3.0");
            }catch(e){}
            try {
                return new ActiveXObject("Msxml2.XMLHTTP");
            }catch(e){}
            try {
                return new ActiveXObject("Microsoft.XMLHTTP");
            }catch(e){}
            return null;
        };

        this.setGetURLParameters = function (parameters) {
            let settingParameters = "";

            if(typeof parameters === "string" || typeof parameters === "object")
            {
                try {
                    // noinspection OverlyComplexBooleanExpressionJS
                    if(typeof parameters === "string" && parameters.length === 0 || typeof parameters === "object" && libertyLibrary.getObjectLength(parameters) === 0)
                    {
                        throw 0;
                    }

                    if (typeof parameters === "string") {
                        settingParameters = parameters;
                    }

                    else {
                        Object.keys(parameters).forEach(function (key) {
                            settingParameters += key + "=" + parameters[key] + "&";
                        });

                        settingParameters = settingParameters.substring(0, settingParameters.length - 1);
                    }

                    if (extraParameters.length === 0) {
                        extraParameters = "?";
                    }

                    extraParameters += settingParameters;
                    return true;
                }

                catch (e)
                {
                    console.error("The parameter data you have passed is empty. Please try again!");
                    return false;
                }
            }

            console.error("The parameter data you have passed is not accepted by us. Please try again!");
            return false;
        };

        this.toWebServer = function()
        {
            serverURL+="/webserver.php";
        };

        this.resetGetURLParameters = function()
        {
            extraParameters = "";
        };

        this.makeConnection = function(parameters, callback = null, additionalRules = {})
        {
            let http = createConObj(), param;

            if(typeof additionalRules.dataException !== "boolean" || typeof additionalRules.dataException === "boolean" && !additionalRules.dataException) {
                // noinspection OverlyComplexBooleanExpressionJS
                if (typeof parameters !== "object" || typeof parameters.action !== "string" || typeof callback !== "function" && typeof callback !== "object") {
                    return false;
                }

                parameters = libertyLibrary.base64.enc2JSON(parameters);
            }

            http.open("POST", serverURL + extraParameters, true);

            //Send the proper header information along with the request
            //http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            http.onreadystatechange = function()
            {
                if(http.readyState === 4)
                {
                    param = libertyLibrary.base64.dec(http.responseText);

                    if(typeof callback === "function")
                    {
                        callback(param);
                    }

                    else if(typeof callback === "object")
                    {
                        if(http.status === 200)
                        {
                            if(param.results === "Success") {
                                if (typeof callback.Success === "function") {
                                    callback.Success(param);
                                }
                            }

                            else {
                                if (typeof callback.Fail === "function") {
                                    callback.Fail(param);
                                }
                            }
                        }

                        else
                        {
                            if (typeof callback.Error === "function") {
                                callback.Error(http.status);
                            }
                        }
                    }
                }
            };

            http.send(parameters);
            return true;
        };
    },
    table : {
        insert : function (tableEl, dataToDisplay)
        {
            if(document.getElementById(tableEl))
            {
                libertyLibrary.table.clear(tableEl);
                //tableEl = document.getElementById(tableEl);
                let table = [];

                for (let i = 0; i < dataToDisplay.length; i++)
                {
                    table[i] = {counter:0, row : libertyLibrary.el(tableEl).getEl().insertRow(i + 1), cells:[]};

                    Object.keys(dataToDisplay[i]).forEach(function(key)
                    {
                        table[i].cells[table[i].counter] = table[i].row.insertCell(table[i].counter-1);
                        table[i].cells[table[i].counter].innerHTML = dataToDisplay[i][key];
                    });
                }
            }

            else
            {
                console.error("The table with ID '" + tableID + "' does not exist on the page.");
            }
        },
        sort : function (table, n)
        {
            let tableEl = libertyLibrary.el(table).getEl(), rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            switching = true;
            //Set the sorting direction to ascending:
            dir = 'asc';
            /*Make a loop that will continue until
            no switching has been done:*/
            while (switching) {
                //start by saying: no switching is done:
                switching = false;
                rows = tableEl.rows;
                /*Loop through all table rows (except the
                first, which contains table headers):*/
                for (i = 1; i < (rows.length - 1); i++) {
                    //start by saying there should be no switching:
                    shouldSwitch = false;
                    /*Get the two elements you want to compare,
                    one from current row and one from the next:*/
                    x = rows[i].getElementsByTagName('TD')[n];
                    y = rows[i + 1].getElementsByTagName('TD')[n];
                    /*check if the two rows should switch place,
                    based on the direction, asc or desc:*/
                    if (dir === 'asc') {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            //if so, mark as a switch and break the loop:
                            shouldSwitch= true;
                            break;
                        }
                    } else if (dir === 'desc') {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            //if so, mark as a switch and break the loop:
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    /*If a switch has been marked, make the switch
                    and mark that a switch has been done:*/
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    //Each time a switch is done, increase this count by 1:
                    switchcount ++;
                } else {
                    /*If no switching has been done AND the direction is 'asc',
                    set the direction to 'desc' and run the while loop again.*/
                    if (switchcount === 0 && dir === 'asc') {
                        dir = 'desc';
                        switching = true;
                    }
                }
            }
        },
        clear : function (tableID) {
            let rows = libertyLibrary.el(tableID).getEl().rows;
            let i = rows.length;
            while (--i) {
                rows[i].parentNode.removeChild(rows[i]);
            }
        }
    },
    modal : function (settings)
    {
        let _allowUsage = true, _loadModal, _setModalLayout, _titleEl, _setContainer, _setHeader, _setContent, _setOpenBtn, _showModal, _hideModal;
        let _modalID, _modalEl, _btnID, _btnEl, _containerEl, _contentEl, _headerEl;

        _loadModal = function()
        {
            if(typeof settings === "object")
            {
                if(typeof settings.modalID === "string")
                {
                    _setModalLayout();
                    _setContainer();
                    _setHeader();
                    _setContent();
                }

                if(typeof settings.buttonID === "string")
                {
                    _setOpenBtn();
                }
            }
        };

        _setModalLayout = function ()
        {
            try
            {
                _modalID = settings.modalID;
                _modalEl = document.getElementById(_modalID);
                _modalEl.addEventListener("click", _hideModal);
            }

            catch (e)
            {
                console.error("This modal element with the ID '" +  _modalID + "' does not exist.");
                _allowUsage = false;
            }
        };

        _setContainer = function()
        {
            try
            {
                _containerEl = _modalEl.getElementsByClassName("modal_container")[0];
            }

            catch (e)
            {
                console.error("The container element in the modal ID '" +  _modalID + "' does not exist.");
                _allowUsage = false;
            }
        };

        _setHeader = function()
        {
            try
            {
                _headerEl = _containerEl.getElementsByClassName("modal_header")[0];
                _titleEl =  _headerEl.getElementsByClassName("modal_title")[0];
            }

            catch (e)
            {
                console.error("This header or title element in the modal ID '" +  _modalID + "' does not exist.");
                _allowUsage = false;
            }
        };

        _setContent = function()
        {
            try
            {
                _contentEl = _containerEl.getElementsByClassName("modal_content")[0];
            }

            catch (e)
            {
                console.error("This content element in the modal ID '" +  _modalID + "' does not exist.");
                _allowUsage = false;
            }
        };

        _setOpenBtn = function ()
        {
            try
            {
                _btnID = settings.buttonID;
                _btnEl = document.getElementById(_btnID);
                _btnEl.addEventListener("click", _showModal);
            }

            catch (e)
            {
                console.error("This button element with the ID '" +  _btnID + "' does not exist." + e);
            }
        };

        _showModal = function(content = {})
        {
            if(_allowUsage)
            {
                if(typeof content === "object")
                {
                    if(typeof content.title === "string")
                    {
                        _titleEl.innerHTML = content.title;
                    }

                    if(typeof content.body === "string")
                    {
                        _contentEl.innerHTML = content.body;
                    }
                }

                _modalEl.classList.add("modal_open");
            }
        };

        this.show = function (content = {})
        {
            _showModal(content);
        };

        _hideModal = function(content = {})
        {
            if(_allowUsage)
            {
                if(typeof content.target !== "undefined" && typeof content.target.classList === "object")
                {
                    if(!content.target.classList.contains("modal_close")) {
                        return;
                    }
                }

                _modalEl.classList.remove("modal_open");
            }
        };

        this.hide = function(content = {})
        {
            _hideModal(content);
        };

        _loadModal();
    },
    el : function (id)
    {
        let _el, f = {};

        if(document.getElementById(id))
        {
            _el = document.getElementById(id);

            return {
                show: function()
                {
                    _el.classList.remove("hideEl");
                },
                hide: function()
                {
                    _el.classList.add("hideEl");
                },
                fadein : function ()
                {
                    f.show();
                    _el.classList.add("fadeIn");
                    _el.classList.add("animated");
                    setTimeout(function(){
                        _el.classList.remove("fadeIn");
                        _el.classList.remove("animated");
                    }, 1000);
                },
                fadeout: function ()
                {
                    _el.classList.add("fadeOut");
                    _el.classList.add("animated");
                    setTimeout(function(){
                        f.hide();
                        _el.classList.remove("fadeOut");
                        _el.classList.remove("animated");
                    }, 1000);
                },
                inner: function(t)
                {
                    _el.innerHTML = t;
                },
                val: function(v = null)
                {
                    if(v === null)
                    {
                        return _el.value;
                    }

                    else if(typeof v === "string")
                    {
                        _el.value = v;
                    }
                },
                getEl : function ()
                {
                    return _el;
                }
            };
        }

        console.error("Couldn't find the element with ID '" + id + "'. ");
        return f;
    },
    filterInput : {
        isEmailFormat : function(requestedData)
        {
            let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(requestedData).toLowerCase());
        },
        isFloat : function(val)
        {
            return !(!libertyLibrary.isFloat(val) || Number(val) < 0.01);
        },
        isInput : function(e)
        {
            let inputTag = ["input", "textarea", "select"];

            return inputTag.indexOf(e.target.tagName.toLowerCase()) > -1;
        },
        betweenNums : function(min, x, max)
        {
            return x >= min && x < max;
        },
    },
    isEmailFormat : function(requestedData)
    {
        let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(requestedData).toLowerCase());
    }
};

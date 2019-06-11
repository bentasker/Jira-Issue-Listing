var srcht = '';

function runsearch(jsonpage){
    var term = getWin().srcht.toLowerCase(),
    json = JSON.parse(jsonpage),
    i,e;
    
    /* Title matches */
    var m = [];

    srchttl = getWin().srchtit;
    srchsmlr = getWin().srchsim;
    srchkywrds = getWin().srchkw;
    srchdsc = getWin().srchdsc;
    
    for (i=0; i<json.items.length; i++){
        
        e = json.items[i];
        
        if (e['Name'].toLowerCase().indexOf(term) !== -1){
                e['matchtype'] = 'title';
                m.push(e);
                continue;
        }
        
        if (e['Key'].toLowerCase().indexOf(term) !== -1){
                e['matchtype'] = 'Key';
                m.push(e);
                continue;
        }        
        
    }
     
    
    writeResultsTable(m);
    return true;

}


/* Push output into the results table. */
function writeResultsTable(matches){

    var m,h,k,se;
    
    se = escapeHtml(getWin().srcht);
    
    /* Write the search term to the header */
    grabEle('searchterm').innerHTML = se;
    var searchtitle = grabEle('searchtitle');
    searchtitle.setAttribute('style','display: block');


    /* Most search engine bots now run javascript on the page, so there's some benefit to setting page title, meta-keywords etc */
    
    /* The weird concatenation of the space is because of a quirk in my minification routine */
    var pt = 'Search:' + ' "' + se + '"';
    
    if (grabEle('sitename')){
        pt += " - " + grabEle('sitename').content;
    }
    
    document.title = pt;
    var container = grabEle('resultswrapper');
    
    /* Make sure we remove any results that were already there */
    container.innerHTML = '';


    
    for (var i=0; i<matches.length; i++){
        
        m = matches[i];
        
        var rslt = mkEle('div');
        rslt.className = 'result';
        
        var rslttitle = mkEle('div');
        rslttitle.className = 'rslttitle';
        
        
        if (m['href'].substring(0,1) == "/"){
            m['href'] = m['href'].substring(1);
        }
        
        var rsltlink = mkEle('a');
        rsltlink.setAttribute('href',m['href'].replace(/\/json\//,'').replace(/\.json$/,'.html'));
        rsltlink.setAttribute('title',m['Name']);
        rsltlink.innerHTML = escapeHtml(m['Key'] + ": " + m['Name']);
        
        /* Add them to the result div */
        rslttitle.appendChild(rsltlink);
        rslt.appendChild(rslttitle);
        
        var mtype = mkEle('div');
        mtype.className='rsltmtype';
        mtype.innerHTML = "match type: " + m['matchtype'];
        rslt.append(mtype);

        
        /* Write into the document body */
        container.appendChild(rslt);
        
    }
    
}


function triggerSearch(t){
    /* Set the search term */
    getWin().srcht = t;   
    
    buildPageFragment();

    /* Get a copy of the main sitemap */
    var cb = runsearch,
    ecb = console.log;
    
    fetchPage('sitemap.json',cb,ecb);
}


/* When the page first loads, we check if the fragment is present, and search based on it if it is */
function doAutoSearch(){
    
    
    if (getWin().location.hash.length > 0 && getWin().location.hash.indexOf('srchtrm') !== -1){
        
        /* Decode the fragment */
        var fd = parseQSTypeString(getWin().location.hash);
        var t = fd['srchtrm'];
        t = decodeURI(t);
        
        /* Set the searchbox to contain the phrase */
        grabEle('searchterms').value = escapeHtml(t);
        
        getWin().srchL = fd['searchLang'];
        triggerSearch(t);
    }
}


function handleFormSubmit(){
 var t = grabEle('searchterms').value;
 
 
 triggerSearch(t);
 return false;
}



/*         LANGUAGE FILTERS                */







/*             Fragment handling    */


/* Build a URL fragment to store the search settings (allowing sharing of the link) */
function buildPageFragment(){
    var d = {},
    st = '';
    
    d['srchtrm'] = encodeURI(getWin().srcht);
    getWin().location.hash = buildQSTypeString(d);
}



/*        UTILITY FUNCTIONS              */


/* Utility function to parse a string as if it were a querystring and return the resulting pairs as a dict */
function parseQSTypeString(qs){
    
    /* Strip any leading cruft */
    qs = qs.replace(/\?/,'').replace(/^#/,'');
    
    var result = {};
    var splitele;
    
    /* Break the pairs up */
    var pairs = qs.split('&');
    
    /* Iterate over and populate our dict */
    for (var i=0; i<pairs.length; i++){
        splitele = pairs[i].split("=");
        
        /* There may be a trailing ampersand. Do nothing */
        if (splitele[0].length > 0){
            result[splitele[0]] = decodeURI(splitele[1]);
        }
    }
    return result;
}


/* utility function, take a dict and turn it into a query string */
function buildQSTypeString(data){
    var qs = '';
    for (var key in data){
        qs += key + "=" + encodeURI(data[key]) + "&";
    }
    return qs;
}


/* From https://stackoverflow.com/a/6234804 */
function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
 }
 
 
function fetchPage(url,callback,errcallback){
    /* From https://snippets.bentasker.co.uk/page-1708042214-Place-AJAX-GET-request-and-trigger-callback-function-with-result-Javascript.html */
    var xmlhttp;
    if (getWin().XMLHttpRequest){
        /* code for IE7+, Firefox, Chrome, Opera, Safari */
        xmlhttp=new XMLHttpRequest();
    }else{
        /* code for IE6, IE5 (why am I still supporting these? Die... Die.... Die.... */
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4){

            if (xmlhttp.status==200){
                callback(xmlhttp.responseText);
            }else{
                errcallback(xmlhttp.responseText);
            }
        }
    };

    xmlhttp.open("GET",url,true);
    xmlhttp.send();
}

/* Used to help bring down the size of the JS file as can now replace repeated 'document.getElementById' calls with a shorter one */
function grabEle(name){
    return document.getElementById(name);
}

function mkEle(type){
    return document.createElement(type);
}

function getWin(){
    return window;
}
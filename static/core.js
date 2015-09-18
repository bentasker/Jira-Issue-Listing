
function toggleTimeWork(ele){
  
    var st = document.getElementById('tmwrksbtsks');
    var is = document.getElementById('tmwrk');    
    if (ele.checked){
	st.style.display = 'block';
	is.style.display = 'none';
    }else{
 	st.style.display = 'none';
	is.style.display = 'block';     
    }
  
}
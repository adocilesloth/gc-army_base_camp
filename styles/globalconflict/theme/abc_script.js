function show_medals(id)
{
	var x = document.getElementById("camp_"+id);
	if (x.style.display === "none")
	{
		x.style.display = "grid";
	}
	else
	{
		x.style.display = "none";
	}
}

function show_previous_signups(id)
{
	var x = document.getElementById("histogram_"+id.toString());
	x.style.display = "none";
	
	var id_prev = id-1;
	var y = document.getElementById("histogram_"+id_prev.toString());
	y.style.display = "block";
}

function show_next_signups(id)
{
	var x = document.getElementById("histogram_"+id.toString());
	x.style.display = "none";
	
	var id_nxt = id+1;
	var y = document.getElementById("histogram_"+id_nxt.toString());
	y.style.display = "block";
}
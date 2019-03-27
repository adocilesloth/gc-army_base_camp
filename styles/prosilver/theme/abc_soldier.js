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
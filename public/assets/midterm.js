'use strict';
document.querySelectorAll('[data-table-search]').forEach(input=>input.addEventListener('input',()=>{
 const rows=[...document.querySelectorAll('[data-search-row]')];const q=input.value.trim().toLocaleLowerCase();let count=0;
 rows.forEach(row=>{row.hidden=!row.textContent.toLocaleLowerCase().includes(q);if(!row.hidden)count++;});
 document.querySelector('[data-search-empty]').hidden=count>0;
}));
document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm(form.dataset.confirm))event.preventDefault();}));
document.querySelector('[data-image-input]')?.addEventListener('change',event=>{
 const file=event.target.files[0];if(!file)return;const preview=document.querySelector('[data-image-preview]');
 if(preview.dataset.blob)URL.revokeObjectURL(preview.dataset.blob);preview.dataset.blob=URL.createObjectURL(file);preview.src=preview.dataset.blob;
});
document.querySelector('#print-receipt')?.addEventListener('click',()=>window.print());
const productSelect=document.querySelector('#product_id');
if(productSelect){
 const quantity=document.querySelector('#quantity'),cash=document.querySelector('#cash_received'),money=cents=>new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(cents/100);
 function recalculate(){const option=productSelect.selectedOptions[0];const stock=Number(option?.dataset.stock||0),price=Math.round(Number(option?.dataset.price||0)*100),qty=Number(quantity.value||0),total=price*qty;document.querySelector('#sale-total').textContent=money(total);document.querySelector('#sale-change').textContent=money(Math.max(0,Math.round(Number(cash.value||0)*100)-total));document.querySelector('#stock-message').textContent=productSelect.value?`${stock} unit(s) available${qty>stock?' — please reduce the quantity.':''}`:'Choose a product to see available stock.';quantity.max=String(Math.min(9999,stock||9999));}
 [productSelect,quantity,cash].forEach(input=>input.addEventListener('input',recalculate));
 document.querySelectorAll('[data-select-product]').forEach(button=>button.addEventListener('click',()=>{productSelect.value=button.dataset.selectProduct;quantity.value='1';recalculate();productSelect.focus();}));
 let category='All items';const search=document.querySelector('#menu-search');
 function filter(){const q=search.value.trim().toLocaleLowerCase();let count=0;document.querySelectorAll('[data-select-product]').forEach(button=>{button.hidden=(category!=='All items'&&button.dataset.category!==category)||!button.dataset.name.includes(q);if(!button.hidden)count++;});document.querySelector('#no-menu-results').hidden=count>0;}
 document.querySelectorAll('[data-category-filter]').forEach(button=>button.addEventListener('click',()=>{category=button.dataset.categoryFilter;document.querySelectorAll('[data-category-filter]').forEach(b=>{b.classList.toggle('selected',b===button);b.setAttribute('aria-pressed',b===button?'true':'false');});filter();}));search.addEventListener('input',filter);recalculate();
}

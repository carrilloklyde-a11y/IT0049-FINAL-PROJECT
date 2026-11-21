<?php
function peso($value): string { return '₱'.number_format((float)$value,2); }
function initials(string $name): string { return implode('',array_map(fn($part)=>mb_substr($part,0,1),array_slice(explode(' ',trim($name)),0,2))); }
function image_url(?string $filename): string { return $filename ? site_url('media/'.$filename) : base_url('assets/placeholder.svg'); }

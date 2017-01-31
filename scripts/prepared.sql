ELECT r.RDB$PARAMETER_NAME ParName, F.RDB$FIELD_TYPE ParType
FROM RDB$PROCEDURE_PARAMETERS r
JOIN RDB$FIELDS F ON(F.RDB$FIELD_NAME = R.RDB$FIELD_SOURCE)
WHERE  r.RDB$PROCEDURE_NAME = 'QRESULT'
   AND r.RDB$PARAMETER_TYPE = 1
ORDER BY r.RDB$PARAMETER_TYPE, r.RDB$PARAMETER_NUMBER


select 
		min(nomer) as minnomer, 
		max(nomer) as maxnomer, 
		obekti.id from smetki
		join obektinames on smetki.obektnameid = obektinames.id
		join obekti on obektinames.obekt like obekti.name
		where smetki.prdate > '".$from." ".$hours.":00:00' and smetki.prdate < '".$to." ".$hours.":00:00'
		group by obekti.id

//GRUPIRANA REALIZACIQ
select
obekti.name,
artikulnames.artikul,
sum(smetkisdr.kol) as kol,
avg(smetkisdr.salesprice) as salesprice,
avg(smetkisdr.doctprice) doctprice,
sum (smetkisdr.kol*smetkisdr.salesprice) as sumsales,
sum (smetkisdr.kol*smetkisdr.doctprice) as sumdoct,
obekti.id
from smetkisdr
join obekti on smetkisdr.obektid = obekti.id
join artikulnames on smetkisdr.artikulid = artikulnames.id
where
smetkisdr.nomer between 100 and 20000
and obekti.id = 1
group by obekti.name, obekti.id, artikulnames.artikul
order by obekti.name, artikulnames.artikul










	 
	

BALANS KLIENT

SELECT
prdate data,
obektinames.obekt obekt,
partnernames.partnername partner,
(select sum(kol*salesprice) from smetkisdr where smetkisdr.nomer = smetki.nomer and smetkisdr.obektid = smetki.obektid) as suma,
nomer nomer,
docno dokument,
'sale' as status
from smetki
join obektinames on smetki.obektnameid = obektinames.id
join partnernames on smetki.partnernameid = partnernames.id 
where partnernameid = 1500 and razcr = 1
and prdate < ""
union
select
dtdate data,
obektinames.obekt obekt,
partnernames.partnername partner,
suma suma,
smetkano nomer,
docno dokument,
'pay' as status
from moneyoper
join obektinames on moneyoper.obektnameid = obektinames.id
join partnernames on moneyoper.frompartnernameid = partnernames.id
where frompartnernameid = 1500





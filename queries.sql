select * from employees order by employees_id;
# Get employeess with absence and presence count in date range 
select employees.name, 
sum(case when status = 'presence' then 1 else 0 end) as presence_count,
sum(case when status = 'absence' then 1 else 0 end) as absence_count 
 from employees_attendance
 inner join employees on employeess.id = employees_attendance.employees_id
 where date >= '2020-11-01' and date <= '2020-11-16'
 group by employees_id;
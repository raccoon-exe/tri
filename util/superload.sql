-- superload.sql
--
-- Load a large number of rows into a data mart by using a staging table and
-- stored procedures.

-- Select the database
use toxic_release

-- Empty the staging table.
truncate table toxic_release.Staging;

-- Enter our critical section
start transaction;

load data local infile '~/Public/out.csv'
into table toxic_release.Staging
columns terminated by ';' optionally enclosed by '\''
lines terminated by '\n' ignore 1 lines
(doc_ctrl_num, year, facility_serial, facility_name, street_address, city, county, state, zip,
    latitude, longitude, chemical, cas, carcinogen, units, total_release);

-- Jam that data into the db!
commit;

drop procedure if exists process_staging_data;

-- Change delimiter to '!' to prevent procedure from being interpreted by mysql
delimiter !

create procedure process_staging_data()
begin

    -- Load year
    insert ignore into toxic_release.Time
    (Year)
    select distinct year
    from Staging;

    -- Load location
    insert ignore into toxic_release.Location
    (Facility_Serial, Facility_Name, Street_Address, City, County, State, Zip, Latitude,
        Longitude)
    select distinct facility_serial, facility_name, street_address, city, county, state, zip,
                    latitude, longitude
    from Staging;

    -- Load chemical
    insert ignore into toxic_release.Chemical
    (Cas_Number, Chemical_Name, Carcinogen)
    select distinct cas, chemical, carcinogen
    from Staging;

    -- Populate Fact Table
    insert ignore into toxic_release.Release_Facts
    (Location_Id, Time_Id, Chemical_Id, Document_Number, Release_Metric, Release_Amount)
    select distinct Location_Id, Time_Id, Chemical_Id, doc_ctrl_num, units,
                    total_release
    from Time, Location, Chemical, Staging
    where Chemical.Cas_Number = Staging.cas and
          Location.Facility_Serial = Staging.facility_serial and
          Time.Year = Staging.year;

end!
delimiter ;

call process_staging_data()

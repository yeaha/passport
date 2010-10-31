--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: passport; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA passport;


--
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: -
--

CREATE OR REPLACE PROCEDURAL LANGUAGE plpgsql;


SET search_path = passport, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: entity; Type: TABLE; Schema: passport; Owner: -; Tablespace: 
--

CREATE TABLE entity (
    sn uuid NOT NULL,
    email character varying(255) NOT NULL,
    passwd character(32) NOT NULL,
    create_time timestamp(0) with time zone DEFAULT now() NOT NULL,
    update_time timestamp(0) with time zone DEFAULT now() NOT NULL
);


--
-- Name: entity_pkey; Type: CONSTRAINT; Schema: passport; Owner: -; Tablespace: 
--

ALTER TABLE ONLY entity
    ADD CONSTRAINT entity_pkey PRIMARY KEY (sn);


--
-- Name: ix_entity_email; Type: INDEX; Schema: passport; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX ix_entity_email ON entity USING btree (email);


--
-- PostgreSQL database dump complete
--


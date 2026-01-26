--
-- PostgreSQL database dump
--

\restrict 7zyy4kyMadozMNyRu3M0jR5wfiuT6dhLzyRimenUKYsJopm0qng6lRBmg3hbGGz

-- Dumped from database version 18.1 (Debian 18.1-1.pgdg13+2)
-- Dumped by pg_dump version 18.1 (Debian 18.1-1.pgdg13+2)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: appointments; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.appointments (
    id integer NOT NULL,
    business_id integer NOT NULL,
    user_id integer,
    appointment_date timestamp without time zone NOT NULL,
    status character varying(50) DEFAULT 'pending'::character varying,
    type character varying(100) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    service_id integer,
    is_reviewed boolean DEFAULT false
);


ALTER TABLE public.appointments OWNER TO docker;

--
-- Name: appointments_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.appointments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.appointments_id_seq OWNER TO docker;

--
-- Name: appointments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.appointments_id_seq OWNED BY public.appointments.id;


--
-- Name: businesses; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.businesses (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    nip character varying(15),
    category character varying(100),
    image_url text,
    rating numeric(3,2) DEFAULT 0.00,
    review_count integer DEFAULT 0,
    email character varying(255),
    city character varying(100),
    street character varying(255),
    house_number character varying(20),
    postal_code character varying(10),
    phone character varying(20),
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    rating_avg numeric(3,2) DEFAULT 0.00
);


ALTER TABLE public.businesses OWNER TO docker;

--
-- Name: businesses_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.businesses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.businesses_id_seq OWNER TO docker;

--
-- Name: businesses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.businesses_id_seq OWNED BY public.businesses.id;


--
-- Name: categories; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.categories (
    id integer NOT NULL,
    name character varying(100) NOT NULL
);


ALTER TABLE public.categories OWNER TO docker;

--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.categories_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_id_seq OWNER TO docker;

--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: reviews; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.reviews (
    id integer NOT NULL,
    business_id integer,
    user_id integer,
    rating integer NOT NULL,
    comment text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT reviews_rating_check CHECK (((rating >= 1) AND (rating <= 5)))
);


ALTER TABLE public.reviews OWNER TO docker;

--
-- Name: reviews_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.reviews_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.reviews_id_seq OWNER TO docker;

--
-- Name: reviews_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.reviews_id_seq OWNED BY public.reviews.id;


--
-- Name: services; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.services (
    id integer NOT NULL,
    business_id integer,
    name character varying(150) NOT NULL,
    price numeric(10,2) NOT NULL,
    duration_minutes integer NOT NULL,
    description text,
    start_hour time without time zone DEFAULT '09:00:00'::time without time zone,
    end_hour time without time zone DEFAULT '17:00:00'::time without time zone
);


ALTER TABLE public.services OWNER TO docker;

--
-- Name: services_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.services_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.services_id_seq OWNER TO docker;

--
-- Name: services_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.services_id_seq OWNED BY public.services.id;


--
-- Name: user_business; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.user_business (
    user_id integer NOT NULL,
    business_id integer NOT NULL,
    role character varying(50) DEFAULT 'owner'::character varying,
    assigned_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_business OWNER TO docker;

--
-- Name: users; Type: TABLE; Schema: public; Owner: docker
--

CREATE TABLE public.users (
    id integer NOT NULL,
    firstname character varying(100) NOT NULL,
    lastname character varying(100) NOT NULL,
    email character varying(150) NOT NULL,
    password character varying(255) NOT NULL,
    bio text,
    enabled boolean DEFAULT true,
    image_url text DEFAULT 'https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok'::text,
    email_notifications boolean DEFAULT true,
    sms_notifications boolean DEFAULT false
);


ALTER TABLE public.users OWNER TO docker;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: docker
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO docker;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: docker
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: appointments id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.appointments ALTER COLUMN id SET DEFAULT nextval('public.appointments_id_seq'::regclass);


--
-- Name: businesses id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.businesses ALTER COLUMN id SET DEFAULT nextval('public.businesses_id_seq'::regclass);


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: reviews id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.reviews ALTER COLUMN id SET DEFAULT nextval('public.reviews_id_seq'::regclass);


--
-- Name: services id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.services ALTER COLUMN id SET DEFAULT nextval('public.services_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: appointments; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.appointments (id, business_id, user_id, appointment_date, status, type, created_at, service_id, is_reviewed) FROM stdin;
1	5	8	2024-09-02 09:00:00	completed	standard	2026-01-25 17:00:55.166309	\N	f
2	5	15	2026-01-25 12:00:00	completed	standard	2026-01-25 17:17:16.382069	1	f
4	7	15	2026-01-27 13:00:00	cancelled	standard	2026-01-25 17:31:30.31546	2	f
3	5	15	2026-01-28 10:30:00	cancelled	standard	2026-01-25 17:19:46.955118	1	f
6	5	15	2026-01-27 09:01:00	pending	standard	2026-01-25 17:52:21.830212	1	f
5	5	15	2026-01-25 12:50:00	completed	standard	2026-01-25 17:52:00.270329	1	t
7	7	15	2026-01-28 09:30:00	pending	standard	2026-01-25 18:27:36.700062	2	f
\.


--
-- Data for Name: businesses; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.businesses (id, name, nip, category, image_url, rating, review_count, email, city, street, house_number, postal_code, phone, description, created_at, rating_avg) FROM stdin;
1	Gentleman's Cut	\N	Fryzjerstwo	https://lh3.googleusercontent.com/aida-public/AB6AXuAPjJb0ReUAijkBGWbtLmXEiJkcTUM5mBh2GpEUIZpn7oSdU9bDF68zbAxukjH2Lt0ALNNWK4Q8tyaJ-mQUiMnh26T1qziS5qIIHm6CFGwflzMOP4L_pjdQgD7GJ-Wm8AYcnf0gwCEwuD-UCPsCevWEBT-JKnFfwIn3OsTgE_jDRfth2HdkFHXLSjz2068Yz5sAMN8Ynpmn3ePQ1RB19tx_fUySWwiFVdaQ6KkUZ4EsxitAqshYmnr6tkuwtXVc5Zl78Zvb7zsh0tM	4.00	123	\N	Warszawa	\N	\N	\N	\N	\N	2025-12-29 22:34:53.859298	0.00
6	Treneiro	123333333	Trening personalny	https://fwczslnevmsrwdiugosm.supabase.co/storage/v1/object/public/bussines_photos/public/1768220501_trener.png	4.70	874	trener@trener.co	Krakow	krakowska	22	121212	123321123	Siema trenuje	2026-01-12 12:21:42.342269	0.00
5	Złomex	12312312	Kosmetyka	https://fwczslnevmsrwdiugosm.supabase.co/storage/v1/object/public/bussines_photos/public/1767112840_zlomex.png	3.25	11000	zlomex@zlomex.com	Krakow	zlomex	123	31-232	1231231312	zlomex	2025-12-30 16:40:41.08276	0.00
7	OKS	123123123	Fitness i trening personalny	https://fwczslnevmsrwdiugosm.supabase.co/storage/v1/object/public/bussines_photos/public/1769361348_oks.jpeg	4.60	401	oks@oks.com	Brzesko	oks strreet	44	32-800	123123123	Jestesmy klubem z Brzeska najlepszy w okolicy lubimy grac w pilke	2026-01-25 17:15:49.153106	0.00
\.


--
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.categories (id, name) FROM stdin;
1	Usługi fryzjerskie
2	Usługi kosmetyczne
3	Medycyna estetyczna
4	Masaż i fizjoterapia
5	Fitness i trening personalny
6	Zdrowie i rehabilitacja
7	Stomatologia
8	Usługi medyczne prywatne
9	Usługi IT i serwis komputerowy
10	Marketing i reklama
11	Fotografia i wideo
12	Edukacja i korepetycje
13	Szkolenia i coaching
14	Usługi prawne
15	Usługi księgowe i podatkowe
16	Nieruchomości i doradztwo mieszkaniowe
17	Usługi remontowo-budowlane
18	Sprzątanie i utrzymanie czystości
19	Motoryzacja i serwis pojazdów
20	Eventy i organizacja wydarzeń
\.


--
-- Data for Name: reviews; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.reviews (id, business_id, user_id, rating, comment, created_at) FROM stdin;
1	5	15	4	Bylo super	2026-01-25 18:15:23.550718
\.


--
-- Data for Name: services; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.services (id, business_id, name, price, duration_minutes, description, start_hour, end_hour) FROM stdin;
1	5	Miedz	50.00	1	Kupie miedz 50 zl za kilogram. Dobra miedz	09:00:00	17:00:00
2	7	Pilka	100.00	30	pilka	09:00:00	17:00:00
\.


--
-- Data for Name: user_business; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.user_business (user_id, business_id, role, assigned_at) FROM stdin;
8	5	owner	2025-12-30 16:40:41.08276
9	6	owner	2026-01-12 12:21:42.342269
15	7	owner	2026-01-25 17:15:49.153106
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: docker
--

COPY public.users (id, firstname, lastname, email, password, bio, enabled, image_url, email_notifications, sms_notifications) FROM stdin;
1	Jan	Kowalski	jan.kowalski@example.com	$2b$10$ZbzQrqD1vDhLJpYe/vzSbeDJHTUnVPCpwlXclkiFa8dO5gOAfg8tq	Lubi programować w JS i PL/SQL.	t	https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok	t	f
2	1	1	1@1.com	$2y$10$oqd4Pp/hthr2AAN7g12p8uQb5e95fzH4kKYnBUGGbUz4.qZwIA6de		t	https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok	t	f
3	2	2	2@2.com	$2y$10$3WmrQTfKc1bkXv8yvUr4dOh.40G21zoURuOfsJlaUJrCY5bsPP8aK		t	https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok	t	f
4	6	6	6@6.com	$2y$10$zbhIZ3D/7EtLx/tGB3mOkeB9JR8j6BdohxnVBDmMb/Tm9NmxIJkJu		t	https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok	t	f
6	fitness	Owner	fitness@fitness.com	$2y$10$SFIg6hXdTLT4gV7SA0K.F.lOfqDZbOXX8Ia9NfrkIek9MEge9W0gi	\N	t	https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok	t	f
9	Treneiro	Owner	trener@trener.co	$2y$10$7qno98zTUzfKi2Q0fhXvi.8UffvYrXLLb5uEul.s7jmT1JPCcN0/a	\N	t	https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok	t	f
11	Piotr	Nowak	piotr@piotr.com	$2y$10$8xhyJVp1SPZEUyLXUbtvBOETGkUf5u01ABYJwBVEk/REIZD8bkeYe	\N	t	https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok	t	f
12	k	k	k@k.com	$2y$10$L4qsztzWYfKJ8KM2p5jIWuEoNuUDX6XPnynfbnbRDfA0ThJMdYp76	\N	t	https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok	t	f
13	a	a	a@a.com	$2y$10$8HW/w.VAvhkY/OyXo42/0O9Z8doJHjxc.WB7ek84gKa/w1Quyc70q	\N	t	https://fwczslnevmsrwdiugosm.supabase.co/storage/v1/object/public/bussines_photos/public/1768223603_trener.png	t	f
14	m	m	m@m.com	$2y$10$R3d5ELJho07JK0sJeeRzPuZSjD.R5tjVP8sGbK8dLye9G5rhsLuCq	\N	t	https://fwczslnevmsrwdiugosm.supabase.co/storage/v1/object/public/bussines_photos/public/1768223673_taco.png	f	f
10	tak	tak	tak@tak.com	$2y$10$SVnFoNCAaXq0hxG6a9BnDeIu9Tt0ELuablVDoyGqrKGXVOqLPJSsa	\N	t	https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok	f	f
8	Złomexxxx	Owner	zlomex@zlomex.com	$2y$10$CK8t/iN9NqlhicI7bPuIy.KGBPgj9lgRhLjEBfPkxparhUAy4Bvve	Siema estem złomex i sprzedje złom i cukier	t	https://fwczslnevmsrwdiugosm.supabase.co/storage/v1/object/public/bussines_photos/public/1768477769_zlom.jpeg	t	f
15	OKS	Owner	oks@oks.com	$2y$10$iquyQjn/RzOsUu3AvEGk4ekn53YmgsO8rKf3.hDC6OKQ2hszvh7xi	siema jestem oks	t	https://fwczslnevmsrwdiugosm.supabase.co/storage/v1/object/public/bussines_photos/public/1769361420_oks1.jpeg	t	f
\.


--
-- Name: appointments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.appointments_id_seq', 7, true);


--
-- Name: businesses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.businesses_id_seq', 7, true);


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.categories_id_seq', 20, true);


--
-- Name: reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.reviews_id_seq', 1, true);


--
-- Name: services_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.services_id_seq', 2, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: docker
--

SELECT pg_catalog.setval('public.users_id_seq', 15, true);


--
-- Name: appointments appointments_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_pkey PRIMARY KEY (id);


--
-- Name: businesses businesses_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.businesses
    ADD CONSTRAINT businesses_pkey PRIMARY KEY (id);


--
-- Name: categories categories_name_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_name_key UNIQUE (name);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: reviews reviews_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.reviews
    ADD CONSTRAINT reviews_pkey PRIMARY KEY (id);


--
-- Name: services services_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.services
    ADD CONSTRAINT services_pkey PRIMARY KEY (id);


--
-- Name: user_business user_business_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.user_business
    ADD CONSTRAINT user_business_pkey PRIMARY KEY (user_id, business_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: appointments appointments_service_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_service_id_fkey FOREIGN KEY (service_id) REFERENCES public.services(id);


--
-- Name: appointments appointments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: user_business fk_business; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.user_business
    ADD CONSTRAINT fk_business FOREIGN KEY (business_id) REFERENCES public.businesses(id) ON DELETE CASCADE;


--
-- Name: user_business fk_user; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.user_business
    ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: reviews reviews_business_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.reviews
    ADD CONSTRAINT reviews_business_id_fkey FOREIGN KEY (business_id) REFERENCES public.businesses(id) ON DELETE CASCADE;


--
-- Name: reviews reviews_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.reviews
    ADD CONSTRAINT reviews_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: services services_business_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: docker
--

ALTER TABLE ONLY public.services
    ADD CONSTRAINT services_business_id_fkey FOREIGN KEY (business_id) REFERENCES public.businesses(id);


--
-- PostgreSQL database dump complete
--

\unrestrict 7zyy4kyMadozMNyRu3M0jR5wfiuT6dhLzyRimenUKYsJopm0qng6lRBmg3hbGGz


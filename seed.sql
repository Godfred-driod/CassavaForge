USE cassavaforge;

INSERT INTO products (id, slug, name, series_code, category, application_grade, description, image_url, display_order, published) VALUES
(UUID(), 'cassava-flex-pack', 'Cassava Flex Pack', 'CF-01', 'packaging', 'Flexible packaging', 'A renewable film platform for lightweight applications where clarity and end-of-life matter.', 'assets/images/packaging.jpg', 1, 1),
(UUID(), 'cassava-clear-film', 'Cassava Clear Film', 'CF-02', 'films', 'Transparent barrier film', 'A clear, adaptable film material for thoughtful product and food-contact concepts.', 'assets/images/films.jpg', 2, 1),
(UUID(), 'cassava-molded-form', 'Cassava Molded Form', 'CF-03', 'cutlery', 'Molded products', 'A practical molded-material base for single-use products that should not outlive their purpose.', 'assets/images/cutlery.jpg', 3, 1);

INSERT INTO blog_posts (id, slug, title, category, excerpt, content, author, read_minutes, published) VALUES
(UUID(), 'why-cassava', 'Why cassava is an important material story', 'Materials', 'Cassava connects agricultural resilience with new possibilities in product design.', 'Good material choices begin with good systems thinking. Cassava grows in climates where other crops can be difficult, and its starch offers a compelling starting point for renewable material development.\n\nThe opportunity is not to replace every material overnight. It is to build credible alternatives, test them honestly, and make the better choice easier to adopt.', 'CassavaForge', 4, 1),
(UUID(), 'designing-for-the-next-cycle', 'Designing for the next cycle', 'Design', 'Performance and responsibility become more useful when they are considered together.', 'A material is never only a material. It is a supply chain, a manufacturing decision, a user experience, and an end-of-life question. Our work is to bring those questions into the same room early enough to make a difference.', 'CassavaForge', 3, 1);

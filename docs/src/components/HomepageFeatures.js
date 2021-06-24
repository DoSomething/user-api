import React from 'react';
import clsx from 'clsx';
import styles from './HomepageFeatures.module.css';

const FeatureList = [
  {
    title: 'Mattis Parturient',
    Svg: require('../../static/img/undraw_docusaurus_mountain.svg').default,
    description: (
      <>
        Nullam quis risus eget urna mollis ornare vel eu leo. Cum sociis natoque
        penatibus et magnis dis parturient montes, nascetur ridiculus mus.
      </>
    ),
  },
  {
    title: 'Aenean Vehicula Ornare',
    Svg: require('../../static/img/undraw_docusaurus_tree.svg').default,
    description: (
      <>
        Nullam id dolor id nibh ultricies vehicula ut id elit. Donec id elit non
        mi porta gravida at eget metus. Sed posuere consectetur est at lobortis.
      </>
    ),
  },
  {
    title: 'Dolor Vehicula Risus',
    Svg: require('../../static/img/undraw_docusaurus_react.svg').default,
    description: (
      <>
        Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Maecenas
        faucibus mollis interdum.
      </>
    ),
  },
];

function Feature({ Svg, title, description }) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Svg className={styles.featureSvg} alt={title} />
      </div>
      <div className="text--center padding-horiz--md">
        <h3>{title}</h3>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}

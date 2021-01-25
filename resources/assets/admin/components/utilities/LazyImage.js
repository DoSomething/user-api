/* global Image */

import React, { useState, useEffect } from 'react';

const EMPTY_IMAGE =
  'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

const LazyImage = ({ className, alt, src }) => {
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let loader = new Image();
    loader.onload = () => setLoading(false);

    // Reset 'loading' to true if we change 'src':
    setLoading(true);

    // Only try to preload if we're given a 'src':
    if (src) {
      loader.src = src;
    }

    // Finally, clean up if this component dismounts:
    return () => (loader = null);
  }, [src]);

  return (
    <img
      className={className}
      alt={alt}
      src={loading ? EMPTY_IMAGE : src}
      style={{
        transition: 'opacity 0.5s',
        opacity: loading ? 0 : 1,
      }}
    />
  );
};

export default LazyImage;

extension Time on DateTime? {
  bool toEqualTime(DateTime? time) {
    if (time?.year != this?.year) {
      return false;
    } else if (time?.month != this?.month) {
      return false;
    } else if (time?.day != this?.day) {
      return false;
    }
    return true;
  }
}
